<?php
// Protocol Battlefield 3 (FULL) compatible avec Venice Unleashed - BF3VU
// Version : alpha 1
// Date de dev : 01/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_bf3_engine(array $server, array $viewer_data): array {
    $ip   = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];

    $query_header = "\xFE\xFD";
    $challenge    = null;

    // Helper pour envoyer une requête GameSpy
    $send_packet = function($type, $append = '') use ($ip, $port, $query_header) {
        $packet = $query_header . chr($type) . "\x04\x05\x06\x07" . $append;
        $sock = fsockopen("udp://$ip", $port, $errno, $errstr, 1);
        if (!$sock) return false;
        socket_set_timeout($sock, 1);
        fwrite($sock, $packet);
        $response = fread($sock, 8192);
        fclose($sock);
        return $response;
    };

    // 1. Obtenir le challenge token
    $challenge_response = $send_packet(0x09);
    if (!$challenge_response || !str_contains($challenge_response, "challenge")) {
        $viewer_data['hostname'] = "Pas de réponse (challenge)";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    // Extraire le token de challenge
    $challenge = trim(substr($challenge_response, 5));
    if (!$challenge) {
        $viewer_data['hostname'] = "Challenge token vide";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    // 2. Requête FULL (type 0x00)
    $response = $send_packet(0x00, $challenge);
    if (!$response || strlen($response) < 10) {
        $viewer_data['hostname'] = "Pas de réponse (full query)";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    $viewer_data['debug_response_hex']   = strtoupper(bin2hex($response));
    $viewer_data['debug_response_ascii'] = preg_replace('/[^(\x20-\x7F)]*/','', $response);
    $viewer_data['debug_timeout']        = 'Non';

    // Découper le bloc de données
    $parts = explode("\x00", substr($response, 16)); // On saute l'en-tête

    $assoc_data = [];
    $i = 0;
    // Lecture des infos du serveur
    while (isset($parts[$i + 1]) && $parts[$i] !== '') {
        $assoc_data[$parts[$i]] = $parts[$i + 1];
        $i += 2;
    }

    $i++; // Sauter le séparateur vide
    $headers = [];
    while (isset($parts[$i]) && $parts[$i] !== '') {
        $headers[] = $parts[$i++];
    }

    $players = [];
    while (isset($parts[$i])) {
        $player = [];
        foreach ($headers as $header) {
            $player[$header] = $parts[$i++] ?? '';
        }
        if (!empty($player['name'])) {
            $players[] = $player;
        }
    }

    // Remplissage des données
    $viewer_data['hostname']     = $assoc_data['hostname'] ?? 'Serveur BF3';
    $viewer_data['map']          = $assoc_data['mapname'] ?? '';
    $viewer_data['mod_folder']   = $assoc_data['gamevariant'] ?? '';
    $viewer_data['nom_jeux']     = 'Battlefield 3';
    $viewer_data['num_players']  = (int)($assoc_data['numplayers'] ?? 0);
    $viewer_data['max_players']  = (int)($assoc_data['maxplayers'] ?? 0);
    $viewer_data['game_type']    = $assoc_data['gametype'] ?? '';
    $viewer_data['online']       = true;
    $viewer_data['players_list'] = [];

    foreach ($players as $p) {
        $viewer_data['players_list'][] = [
            'name'  => $p['name'] ?? '',
            'score' => (int)($p['score'] ?? 0),
            'ping'  => (int)($p['ping'] ?? 0),
            'team'  => $p['team'] ?? '',
        ];
    }

    // Traductions
    $viewer_data['serveur_type']    = '🖥 Serveur dédié';
    $viewer_data['serveur_os']      = '❓ Inconnu';
    $viewer_data['prive_public']    = '🌍 Public';
    $viewer_data['vac']             = 0;
    $viewer_data['vac_human']       = false;
    $viewer_data['anti_cheat']      = '❌ Aucun';

    return $viewer_data;
}