<?php
// Protocol pour Project Zomboid (basé sur Source Engine Query)
// Version : alpha 1.2
// Date : 01/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_pz_engine(array $server, array $viewer_data): array {
    $ip = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];      // Port de connexion (jeu)
    $qport = (int)$server['gserver_qport'];    // Port de query à utiliser ici !

    $base_packet = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";

    // Envoi de paquet UDP et récupération de la réponse
    $send_packet = function($ip, $port, $packet) {
        $sock = fsockopen("udp://$ip", $port, $errno, $errstr, 1);
        if (!$sock) return false;
        socket_set_timeout($sock, 1);
        fwrite($sock, $packet);
        $response = fread($sock, 4096);
        fclose($sock);
        return $response;
    };

    $response = $send_packet($ip, $qport, $base_packet); // 👈 Utilisation du bon port ici

    if (!$response || strlen($response) < 5) {
        $viewer_data['hostname'] = "Pas de réponse ou réponse trop courte";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    $type = ord($response[4] ?? "\x00");

    // Si le serveur demande un challenge token (0x41)
    if ($type === 0x41) {
        $challenge = substr($response, 5, 4);
        $response = $send_packet($ip, $qport, $base_packet . $challenge); // 👈 Même qport

        if (!$response || strlen($response) < 5) {
            $viewer_data['hostname'] = "Pas de réponse au challenge";
            $viewer_data['debug_timeout'] = 'Oui';
            return $viewer_data;
        }

        $type = ord($response[4] ?? "\x00");
        $viewer_data['debug_challenge'] = bin2hex($challenge);
    }

    // Debug brut
    $viewer_data['debug_response_hex']   = strtoupper(bin2hex($response));
    $viewer_data['debug_response_ascii'] = preg_replace('/[^(\x20-\x7F)]*/','', $response);
    $viewer_data['debug_type_hex']       = strtoupper(dechex($type));
    $viewer_data['debug_timeout']        = 'Non';

    if ($type !== 0x49) {
        $viewer_data['hostname'] = "Réponse inattendue : 0x" . strtoupper(dechex($type));
        return $viewer_data;
    }

    // Lecture structurée de la réponse A2S_INFO
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
    $players       = ord($response[$offset++] ?? "\x00");
    $max_players   = ord($response[$offset++] ?? "\x00");
    $bots          = ord($response[$offset++] ?? "\x00");
    $server_type   = $response[$offset++] ?? '';
    $environment   = $response[$offset++] ?? '';
    $visibility    = ord($response[$offset++] ?? "\x00");
    $vac           = ord($response[$offset++] ?? "\x00");

    // Affectation des données principales
    $viewer_data['hostname']              = $hostname;
    $viewer_data['map']                   = $map;
    $viewer_data['mod_folder']            = $folder;
    $viewer_data['nom_jeux']              = $game;
    $viewer_data['num_players']           = $players;
    $viewer_data['max_players']           = $max_players;
    $viewer_data['bots']                  = $bots;
    $viewer_data['server_type']           = $server_type;
    $viewer_data['systeme_exploitation']  = $environment;
    $viewer_data['prive_public']          = $visibility;
    $viewer_data['vac']                   = $vac;
    $viewer_data['steam_app_id']          = $id;
    $viewer_data['protocol']              = $protocol;
    $viewer_data['online']                = true;

    // Formatage humain
    $viewer_data['vac_human']      = ($vac === 1);
    $viewer_data['anti_cheat']     = $vac ? '✅ Protégé par VAC' : '❌ Non protégé VAC';
    $viewer_data['prive_public']   = $visibility ? '🔒 Privé (mot de passe)' : '🌍 Public';

    $viewer_data['serveur_type'] = match ($server_type) {
        'd' => '🖥 Serveur dédié',
        'l' => '👤 Serveur local (listen)',
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
