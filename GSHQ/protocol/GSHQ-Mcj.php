<?php 
// Protocol pour Minecraft Java Edition
// Version : alpha 1
// Date de dev : 31/05/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_mcj_engine(array $server, array $viewer_data): array {
    $ip = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];

    $timeout = 3;
    $start = microtime(true);
    $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);

    if (!$socket) {
        $viewer_data['debug_timeout'] = true;
        return $viewer_data;
    }

    stream_set_timeout($socket, $timeout);

    // Handshake + Status Request
    $data = pack('c*', 0x00, 0x00) . 
            pack('c', strlen($ip)) . $ip . 
            pack('n', $port) . 
            pack('c', 1);

    // Envoi du handshake (packet ID 0x00)
    $packet = pack('c', strlen($data)) . $data;
    fwrite($socket, $packet);

    // Envoi de la requête de status (packet ID 0x00)
    fwrite($socket, "\x01\x00");

    // Lecture de la réponse
    $len = read_varint($socket);
    if ($len < 10) {
        fclose($socket);
        return $viewer_data;
    }

    $packet_id = read_varint($socket);
    $json_length = read_varint($socket);
    $json = fread($socket, $json_length);
    fclose($socket);

    $data = json_decode($json, true);
    if (!is_array($data)) {
        return $viewer_data;
    }

    // Traitement du champ "description"
    $desc = $data['description'] ?? 'Minecraft Java';
    if (is_array($desc)) {
        $viewer_data['hostname'] = $desc['text'] ?? 'Minecraft Java';
    } elseif (is_string($desc)) {
        $viewer_data['hostname'] = $desc;
    } else {
        $viewer_data['hostname'] = 'Minecraft Java';
    }

    // Données principales
    $viewer_data['map']          = '-';
    $viewer_data['num_players']  = $data['players']['online'] ?? 0;
    $viewer_data['max_players']  = $data['players']['max'] ?? 0;
    $viewer_data['version']      = $data['version']['name'] ?? 'Inconnue';
    $viewer_data['protocol']     = 'mcj';
    $viewer_data['online']       = true;
    $viewer_data['debug_timeout'] = false;
    $viewer_data['latency_ms']   = round((microtime(true) - $start) * 1000);

    // Liste des joueurs (si fournie)
    if (!empty($data['players']['sample'])) {
        foreach ($data['players']['sample'] as $player) {
            $viewer_data['players'][] = [
                'name' => $player['name'] ?? 'Inconnu',
                'id'   => $player['id'] ?? null
            ];
        }
    }

    // Favicon (base64)
    if (isset($data['favicon'])) {
        $viewer_data['favicon_base64'] = $data['favicon'];
        $viewer_data['favicon_url_safe'] = htmlspecialchars($data['favicon'], ENT_QUOTES, 'UTF-8');
    }

    return $viewer_data;
}

function read_varint($socket): int {
    $value = 0;
    $position = 0;

    while (true) {
        $c = ord(fread($socket, 1));
        $value |= ($c & 0x7F) << $position;

        if (($c & 0x80) !== 0x80) break;

        $position += 7;
        if ($position >= 35) {
            return 0;
        }
    }
    return $value;
}