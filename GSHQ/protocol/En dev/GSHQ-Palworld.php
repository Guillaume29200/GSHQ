<?php
// Protocol pour Palworld
// Version : alpha 1.0 En developpement non fonctionnel
// Date de dev : 03/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_palworld_query(array $server, array $viewer_data): array {
    $ip = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];
    $qport = (int)$server['gserver_qport'];
    if (!$qport) $qport = $port; // fallback si qport absent

    // Exemple de paquet d'interrogation initiale
    $base_packet = "PALQ\x00";

    $send_packet = function($ip, $port, $packet) {
        $sock = @fsockopen("udp://$ip", $port, $errno, $errstr, 1);
        if (!$sock) return false;

        stream_set_timeout($sock, 1);
        fwrite($sock, $packet);

        $response = '';
        $start = microtime(true);
        while (!feof($sock) && (microtime(true) - $start) < 1) {
            $chunk = fread($sock, 4096);
            if ($chunk === false || $chunk === '') break;
            $response .= $chunk;
        }
        fclose($sock);

        return $response;
    };

    // Requête principale via QPORT
    $response = $send_packet($ip, $qport, $base_packet);

    if (!$response || strlen($response) < 5) {
        $viewer_data['hostname'] = "Pas de réponse ou réponse trop courte";
        $viewer_data['debug_timeout'] = 'Oui';
        $viewer_data['online'] = false;
        return $viewer_data;
    }

    $type = ord($response[0]);

    if ($type === 0x43) { // Challenge
        $challenge = substr($response, 1, 4);
        $response = $send_packet($ip, $qport, $base_packet . $challenge);
        if (!$response || strlen($response) < 5) {
            $viewer_data['hostname'] = "Pas de réponse au challenge";
            $viewer_data['debug_timeout'] = 'Oui';
            $viewer_data['online'] = false;
            return $viewer_data;
        }
        $type = ord($response[0]);
    }

    if ($type !== 0x49) { // 'I' pour Info
        $viewer_data['hostname'] = "Réponse inattendue : 0x" . strtoupper(dechex($type));
        $viewer_data['online'] = false;
        return $viewer_data;
    }

    $offset = 1;
    $readString = function(string $data, int &$offset): string {
        $end = strpos($data, "\x00", $offset);
        if ($end === false) return '';
        $str = substr($data, $offset, $end - $offset);
        $offset = $end + 1;
        return $str;
    };

    $hostname = $readString($response, $offset);
    $map = $readString($response, $offset);
    $game_mode = $readString($response, $offset);
    $num_players = ord($response[$offset++] ?? "\x00");
    $max_players = ord($response[$offset++] ?? "\x00");
    $server_version = $readString($response, $offset);

    $viewer_data['hostname'] = $hostname;
    $viewer_data['map'] = $map;
    $viewer_data['game_mode'] = $game_mode;
    $viewer_data['players'] = $num_players;
    $viewer_data['max_players'] = $max_players;
    $viewer_data['server_version'] = $server_version;
    $viewer_data['online'] = true;

    // Requête liste joueurs (toujours sur QPORT)
    $player_packet = "PALP\x00";
    $response = $send_packet($ip, $qport, $player_packet);

    if (!$response || strlen($response) < 5) {
        $viewer_data['players_list'] = [];
    } else {
        $type = ord($response[0]);
        if ($type === 0x43) {
            $challenge = substr($response, 1, 4);
            $response = $send_packet($ip, $qport, $player_packet . $challenge);
            if (!$response || strlen($response) < 5) {
                $viewer_data['players_list'] = [];
            } else {
                $type = ord($response[0]);
            }
        }

        if ($type === 0x50) { // 'P' joueurs
            $offset = 1;
            $num_players = ord($response[$offset++]);
            $players = [];
            for ($i = 0; $i < $num_players; $i++) {
                $name = $readString($response, $offset);
                $score = unpack('l', substr($response, $offset, 4))[1] ?? 0;
                $offset += 4;
                $time = unpack('f', substr($response, $offset, 4))[1] ?? 0.0;
                $offset += 4;

                $players[] = [
                    'name' => $name,
                    'score' => $score,
                    'time' => round($time, 2),
                ];
            }
            $viewer_data['players_list'] = $players;
        } else {
            $viewer_data['players_list'] = [];
        }
    }

    return $viewer_data;
}