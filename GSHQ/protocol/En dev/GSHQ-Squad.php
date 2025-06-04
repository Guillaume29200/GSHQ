<?php
// Protocol pour Squad (UE4, Valve A2S protocol)
// Sans RCON, juste requête UDP "A2S_INFO"
// Version : alpha 1.0 En developpement non fonctionnel
// Date de dev : 03/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_squad_engine(array $server, array $viewer_data): array {
    $ip   = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];
	$qport = (int)$server['gserver_qport'];

    $A2S_INFO = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";

    $send_packet = function($ip, $port, $packet) {
        $sock = @fsockopen("udp://$ip", $port, $errno, $errstr, 1);
        if (!$sock) return false;
        stream_set_timeout($sock, 1);
        fwrite($sock, $packet);
        $response = fread($sock, 4096);
        fclose($sock);
        return $response;
    };

    $response = $send_packet($ip, $port, $A2S_INFO);
    if (!$response || strlen($response) < 5) {
        $viewer_data['hostname'] = "Pas de réponse ou réponse trop courte";
        $viewer_data['online'] = false;
        return $viewer_data;
    }

    // Réponse attendue commence par 0x49 (I)
    if (ord($response[4]) !== 0x49) {
        $viewer_data['hostname'] = "Réponse inattendue ou serveur non compatible";
        $viewer_data['online'] = false;
        return $viewer_data;
    }

    $offset = 5;

    $readString = function($data, &$offset) {
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
    $id            = unpack('v', substr($response, $offset, 2))[1]; $offset += 2;
    $num_players   = ord($response[$offset++] ?? "\x00");
    $max_players   = ord($response[$offset++] ?? "\x00");
    $bots          = ord($response[$offset++] ?? "\x00");
    $server_type   = $response[$offset++] ?? '';
    $environment   = $response[$offset++] ?? '';
    $visibility    = ord($response[$offset++] ?? "\x00");
    $vac           = ord($response[$offset++] ?? "\x00");

    $viewer_data['hostname']             = $hostname;
    $viewer_data['map']                  = $map;
    $viewer_data['mod_folder']           = $folder;
    $viewer_data['nom_jeux']             = $game;
    $viewer_data['num_players']          = $num_players;
    $viewer_data['max_players']          = $max_players;
    $viewer_data['bots']                 = $bots;
    $viewer_data['server_type']          = $server_type;
    $viewer_data['systeme_exploitation'] = $environment;
    $viewer_data['prive_public']         = ($visibility === 1) ? '🔒 Privé' : '🌍 Public';
    $viewer_data['vac']                  = $vac;
    $viewer_data['steam_app_id']         = $id;
    $viewer_data['protocol']             = $protocol;
    $viewer_data['online']               = true;

    // Traductions simples
    $viewer_data['anti_cheat'] = ($vac === 1) ? '✅ Protégé par VAC' : '❌ Non protégé VAC';
    $viewer_data['serveur_type'] = match ($server_type) {
        'd' => '🖥 Serveur dédié',
        'l' => '👤 Serveur local',
        'p' => '📺 SourceTV',
        default => '❓ Inconnu',
    };
    $viewer_data['serveur_os'] = match ($environment) {
        'w' => '🪟 Windows',
        'l' => '🐧 Linux',
        'm', 'o' => '🍎 macOS',
        default => '❓ Inconnu',
    };

    return $viewer_data;
}
