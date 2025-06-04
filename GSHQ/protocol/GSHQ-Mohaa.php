<?php 
// Protocol Quake III Engine (id Tech 3)
// Compatible avec : Medal of honor Débarquement allié
// Version : alpha 1.2
// Date : 01/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_mohaa_engine(array $server, array $viewer_data): array {
    $ip = $server['adresse_ip'] ?? '';
    if (!$ip) {
        error_log("GSHQ_mohaa_engine: adresse_ip manquante");
        return $viewer_data;
    }

    // On privilégie gserver_port, sinon gserver_qport
    $port = !empty($server['gserver_port']) ? (int)$server['gserver_port'] : (int)$server['gserver_qport'];
    if (!$port) {
        error_log("GSHQ_mohaa_engine: port invalide");
        return $viewer_data;
    }

    $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        error_log("GSHQ_mohaa_engine: échec création socket");
        return $viewer_data;
    }

    // Timeout réception 3 secondes
    $timeout = ['sec' => 3, 'usec' => 0];
    if (!@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout)) {
        error_log("GSHQ_mohaa_engine: échec socket_set_option SO_RCVTIMEO");
        socket_close($socket);
        return $viewer_data;
    }

    // 🛠 Correction ici : on ajoute \x0A (newline) à la requête
    $query = "\xFF\xFF\xFF\xFF\x02getstatus";

    if (@socket_sendto($socket, $query, strlen($query), 0, $ip, $port) === false) {
        error_log("GSHQ_mohaa_engine: échec socket_sendto vers $ip:$port");
        socket_close($socket);
        return $viewer_data;
    }

    $response = '';
    $from = '';
    $from_port = 0;
    $recv_len = @socket_recvfrom($socket, $response, 4096, 0, $from, $from_port);

    socket_close($socket);

    if ($recv_len === false) {
        error_log("GSHQ_mohaa_engine: aucune réponse reçue de $ip:$port");
        return $viewer_data;
    }

    // Pour debug
    $viewer_data['debug_response_ascii'] = trim($response);

    // On vérifie que la réponse contient hostname
    if (stripos($response, 'hostname') === false) {
        error_log("GSHQ_mohaa_engine: réponse invalide reçue");
        return $viewer_data;
    }

    $lines = explode("\n", trim($response));
    if (count($lines) < 2) {
        error_log("GSHQ_mohaa_engine: réponse trop courte");
        return $viewer_data;
    }

    // Variables serveur
    $server_vars = [];
    $vars_raw = explode('\\', $lines[1]);
    for ($i = 1; $i < count($vars_raw) - 1; $i += 2) {
        $key = trim($vars_raw[$i]);
        $value = trim($vars_raw[$i + 1]);
        $server_vars[$key] = $value;
    }

    // Joueurs
    $players = [];
    for ($i = 2; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        if ($line === '') continue;

        if (preg_match('/^(\d+)\s+(\d+)\s+"(.*)"$/', $line, $matches)) {
            $players[] = [
                'score' => (int)$matches[1],
                'ping'  => (int)$matches[2],
                'name'  => $matches[3],
            ];
        }
    }

    $viewer_data['hostname']      = $server_vars['sv_hostname'] ?? 'Serveur sans nom';
    $viewer_data['map']           = $server_vars['mapname'] ?? '';
    $viewer_data['num_players']   = count($players);
    $viewer_data['max_players']   = isset($server_vars['sv_maxclients']) ? (int)$server_vars['sv_maxclients'] : 0;
    $viewer_data['players']       = $players;
    $viewer_data['online']        = true;

    // Champs spécifiques MOHAA (id Tech 3)
    $viewer_data['game_type']     = $server_vars['g_gametype'] ?? null;
    $viewer_data['mod_game']      = $server_vars['gamename'] ?? ($server_vars['fs_game'] ?? null);
    $viewer_data['version']       = $server_vars['version'] ?? null;
    $viewer_data['prive_public']  = isset($server_vars['g_needpass']) ? ($server_vars['g_needpass'] === '1') : null;
    $viewer_data['pure_server']   = isset($server_vars['sv_pure']) ? ($server_vars['sv_pure'] === '1') : null;
    $viewer_data['friendly_fire'] = isset($server_vars['g_friendlyfire']) ? ($server_vars['g_friendlyfire'] === '1') : null;
    $viewer_data['timelimit']     = isset($server_vars['timelimit']) ? (int)$server_vars['timelimit'] : null;
    $viewer_data['fraglimit']     = isset($server_vars['fraglimit']) ? (int)$server_vars['fraglimit'] : null;
	$viewer_data['players_list']  = count($players) > 0;
    $viewer_data['protocol']      = 'mohaa';

    return $viewer_data;
}
