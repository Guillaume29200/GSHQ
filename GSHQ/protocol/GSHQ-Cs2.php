<?php
// Protocol pour Counter-Strike 2
// Version : alpha 1.1
// Date de dev : 03/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_cs2_engine(array $server, array $viewer_data): array {
    $ip   = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];

    $base_packet = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";

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

    // --- A2S_INFO (existant) ---
    $response = $send_packet($ip, $port, $base_packet);

    if (!$response || strlen($response) < 5) {
        $viewer_data['hostname']     = "Pas de réponse ou réponse trop courte";
        $viewer_data['debug_timeout'] = 'Oui';
        $viewer_data['online']       = false;
        return $viewer_data;
    }

    $type = ord($response[4]);

    if ($type === 0x41) { // Challenge demandé
        $challenge = substr($response, 5, 4);
        $response = $send_packet($ip, $port, $base_packet . $challenge);
        if (!$response || strlen($response) < 5) {
            $viewer_data['hostname']     = "Pas de réponse au challenge";
            $viewer_data['debug_timeout'] = 'Oui';
            $viewer_data['online']       = false;
            return $viewer_data;
        }
        $type = ord($response[4]);
    }

    if ($type !== 0x49) {
        $viewer_data['hostname'] = "Réponse inattendue : 0x" . strtoupper(dechex($type));
        $viewer_data['online'] = false;
        return $viewer_data;
    }

    $offset = 5;
    $readString = function(string $data, int &$offset): string {
        $end = strpos($data, "\x00", $offset);
        if ($end === false) return '';
        $str = substr($data, $offset, $end - $offset);
        $offset = $end + 1;
        return $str;
    };

    $protocol      = ord($response[$offset++]);
    $hostname      = $readString($response, $offset);
    $map           = $readString($response, $offset);
    $folder        = $readString($response, $offset);
    $game          = $readString($response, $offset);
    $id_data       = substr($response, $offset, 2);
    $id            = $id_data !== false && strlen($id_data) === 2 ? unpack('v', $id_data)[1] : 0;
    $offset       += 2;
    $num_players   = ord($response[$offset++] ?? "\x00");
    $max_players   = ord($response[$offset++] ?? "\x00");
    $bots          = ord($response[$offset++] ?? "\x00");
    $server_type   = $response[$offset++] ?? '';
    $environment   = $response[$offset++] ?? '';
    $visibility    = ord($response[$offset++] ?? "\x00");
    $vac           = ord($response[$offset++] ?? "\x00");

    // Affectation données basiques
    $viewer_data['hostname']        = $hostname;
    $viewer_data['map']             = $map;
    $viewer_data['mod_folder']      = $folder;
    $viewer_data['game_name']       = $game;
    $viewer_data['players']         = $num_players;
    $viewer_data['max_players']     = $max_players;
    $viewer_data['bots']            = $bots;
    $viewer_data['server_type']     = $server_type;
    $viewer_data['os']              = $environment;
    $viewer_data['visibility']      = $visibility;
    $viewer_data['vac']             = $vac;
    $viewer_data['steam_app_id']    = $id;
    $viewer_data['protocol']        = $protocol;
    $viewer_data['online']          = true;

    // Traductions
    $viewer_data['vac_enabled']    = ($vac === 1);
    $viewer_data['anti_cheat']     = ($vac === 1) ? '✅ Protégé par VAC' : '❌ Non protégé VAC';
    $viewer_data['privacy']        = ($visibility === 1) ? '🔒 Privé (mot de passe)' : '🌍 Public';
    $viewer_data['server_type_str'] = match ($server_type) {
        'd' => '🖥 Serveur dédié',
        'l' => '👤 Serveur local (listen)',
        'p' => '📺 SourceTV',
        default => '❓ Inconnu',
    };
    $viewer_data['os_str'] = match ($environment) {
        'w' => '🪟 Windows',
        'l' => '🐧 Linux',
        'm', 'o' => '🍎 macOS',
        default => '❓ Inconnu',
    };

    // --- NOUVEAU: Requête A2S_PLAYER pour la liste des joueurs ---

    $player_packet = "\xFF\xFF\xFF\xFF\x55"; // 0x55 = A2S_PLAYER
    $response = $send_packet($ip, $port, $player_packet);

    if (!$response || strlen($response) < 5) {
        // Pas de liste joueurs
        $viewer_data['players_list'] = [];
    } else {
        $type = ord($response[4]);
        if ($type === 0x41) { // Challenge demandé
            $challenge = substr($response, 5, 4);
            $response = $send_packet($ip, $port, "\xFF\xFF\xFF\xFF\x55" . $challenge);
            if (!$response || strlen($response) < 5) {
                $viewer_data['players_list'] = [];
            } else {
                $type = ord($response[4]);
            }
        }

        if ($type === 0x44) { // Réponse liste joueurs
            $offset = 5;
            $num_players = ord($response[$offset++]);
            $players = [];
            for ($i = 0; $i < $num_players; $i++) {
                $index = ord($response[$offset++]); // index du joueur (inutile souvent)
                // nom joueur (string \x00 terminated)
                $name_end = strpos($response, "\x00", $offset);
                if ($name_end === false) break;
                $name = substr($response, $offset, $name_end - $offset);
                $offset = $name_end + 1;
                // score (int32, little endian)
                if (strlen($response) < $offset + 4) break;
                $score_data = substr($response, $offset, 4);
                $score = unpack('l', $score_data)[1];
                $offset += 4;
                // temps en jeu (float 4 bytes)
                if (strlen($response) < $offset + 4) break;
                $time_data = substr($response, $offset, 4);
                $time = unpack('f', $time_data)[1];
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