<?php
// Protocol pour Palworld
// Version : alpha 1.1
// Date de dev : 06/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_palworld_engine(array $server, array $viewer_data): array {
    $ip = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];

    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        $viewer_data['online'] = false;
        $viewer_data['error'] = 'Erreur socket_create()';
        return $viewer_data;
    }

    // Paquet de requête à tester (à ajuster si nécessaire)
    $request = "\x01\x00"; // Hypothétique ou à reverse-engineer

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
    socket_sendto($socket, $request, strlen($request), 0, $ip, $port);

    $response = '';
    $from = '';
    $port = 0;
    $bytes = socket_recvfrom($socket, $response, 4096, 0, $from, $port);

    socket_close($socket);

    if ($bytes === false || empty($response)) {
        $viewer_data['online'] = false;
        $viewer_data['error'] = 'Aucune réponse UDP reçue';
        return $viewer_data;
    }

    // Tentative de décodage JSON (selon format attendu)
    $data = json_decode($response, true);
    if (!is_array($data)) {
        $viewer_data['online'] = false;
        $viewer_data['error'] = 'Réponse UDP invalide ou non JSON';
        return $viewer_data;
    }

    // Remplissage des données (adapter aux clés réelles)
    $viewer_data['hostname'] = $data['ServerName'] ?? 'Serveur Palworld';
    $viewer_data['players'] = $data['CurrentPlayers'] ?? 0;
    $viewer_data['max_players'] = $data['MaxPlayers'] ?? 0;
    $viewer_data['map'] = $data['WorldName'] ?? 'Inconnu';
    $viewer_data['game_name'] = 'Palworld';
    $viewer_data['protocol'] = 'palworld';

    $viewer_data['online'] = true;
    return $viewer_data;
}