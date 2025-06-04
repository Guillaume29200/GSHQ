<?php 
// Protocol Quake III Engine (id Tech 3)
// Compatible avec : Star Wars Jedi Knight 2
// Version : alpha 1.2
// Date : 02/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_udp_query(string $ip, int $port, string $query, int $timeout_sec = 2, int $buffer_size = 4096): ?string {
    $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        error_log("GSHQ_udp_query: socket_create failed for $ip:$port");
        return null;
    }

    // Timeout de réception
    $timeout = ['sec' => $timeout_sec, 'usec' => 0];
    if (!@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout)) {
        error_log("GSHQ_udp_query: socket_set_option SO_RCVTIMEO failed for $ip:$port");
        socket_close($socket);
        return null;
    }

    // Envoi de la requête
    if (@socket_sendto($socket, $query, strlen($query), 0, $ip, $port) === false) {
        error_log("GSHQ_udp_query: socket_sendto failed for $ip:$port");
        socket_close($socket);
        return null;
    }

    // Lecture de la réponse
    $response = '';
    $ok = @socket_recvfrom($socket, $response, $buffer_size, 0, $ip, $port);
    socket_close($socket);

    if ($ok === false) {
        error_log("GSHQ_udp_query: socket_recvfrom timeout or error for $ip:$port");
        return null;
    }

    return $response;
}

function GSHQ_jk2_engine(array $server, array $viewer_data): array {
    $ip   = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];

    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        return $viewer_data; // Échec création socket
    }

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);

    $query = "\xFF\xFF\xFF\xFFgetstatus";
    socket_sendto($socket, $query, strlen($query), 0, $ip, $port);

    $buffer = '';
    $from = $ip;
    $from_port = $port;

	if (@socket_recvfrom($socket, $buffer, 4096, 0, $from, $from_port) === false) {
		socket_close($socket);
		return $viewer_data; // Pas de réponse
	}

    socket_close($socket);

    if (stripos($buffer, "hostname") === false) {
        return $viewer_data; // Réponse invalide
    }

    $lines = explode("\n", trim($buffer));
    $server_vars = [];
    $players = [];

    // Ligne 1 ignorée, ligne 2 : variables serveur
    if (isset($lines[1])) {
        $vars_raw = explode('\\', $lines[1]);
        for ($i = 1; $i < count($vars_raw) - 1; $i += 2) {
            $key = trim($vars_raw[$i]);
            $value = trim($vars_raw[$i + 1]);
            $server_vars[$key] = $value;
        }
    }

    // Joueurs (lignes suivantes)
    for ($i = 2; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        if ($line === '') continue;

        if (preg_match('/^(\d+)\s+(\d+)\s+"(.*)"$/', $line, $matches)) {
            $players[] = [
                'name'  => $matches[3],
                'score' => (int)$matches[1],
                'ping'  => (int)$matches[2],
            ];
        }
    }

    // Données principales
    $viewer_data['hostname']      = $server_vars['sv_hostname'] ?? 'Serveur sans nom';
    $viewer_data['map']           = $server_vars['mapname'] ?? '';
    $viewer_data['num_players']   = count($players);
    $viewer_data['max_players']   = isset($server_vars['sv_maxclients']) ? (int)$server_vars['sv_maxclients'] : 0;
    $viewer_data['players']       = $players;
    $viewer_data['online']        = true;

    // Champs spécifiques Quake3 Engine
    $viewer_data['game_type']     = $server_vars['g_gametype'] ?? null;
    $viewer_data['mod_game']      = $server_vars['gamename'] ?? ($server_vars['fs_game'] ?? null);
    $viewer_data['version']       = $server_vars['version'] ?? null;
    $viewer_data['prive_public']  = isset($server_vars['g_needpass']) ? ($server_vars['g_needpass'] === '1') : null;
    $viewer_data['pure_server']   = isset($server_vars['sv_pure']) ? ($server_vars['sv_pure'] === '1') : null;
    $viewer_data['friendly_fire'] = isset($server_vars['g_friendlyfire']) ? ($server_vars['g_friendlyfire'] === '1') : null;
    $viewer_data['timelimit']     = isset($server_vars['timelimit']) ? (int)$server_vars['timelimit'] : null;
    $viewer_data['fraglimit']     = isset($server_vars['fraglimit']) ? (int)$server_vars['fraglimit'] : null;
	$viewer_data['players_list']  = count($players) > 0;
    $viewer_data['protocol']      = 'quake3';

    return $viewer_data;
}