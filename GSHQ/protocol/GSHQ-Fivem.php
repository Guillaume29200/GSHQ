<?php
// Protocol pour FiveM (Cfx.re)
// Version : alpha 1.2
// Date : 08/06/2025
// Auteur : Slymer

function GSHQ_fivem_engine(array $server, array $viewer_data): array {
    $ip   = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];

    $timeout = 2;

    $query_url = "http://$ip:$port/dynamic.json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $query_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $http_code !== 200) {
        $viewer_data['hostname'] = "Pas de réponse (HTTP $http_code)";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        $viewer_data['hostname'] = "Réponse invalide (JSON)";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    // Infos générales
    $viewer_data['hostname']    = $data['hostname'] ?? 'Nom inconnu';
    $viewer_data['map']         = $data['mapname'] ?? '';
    $viewer_data['gametype']    = $data['gametype'] ?? '';
    $viewer_data['locale']      = $data['locale'] ?? '';
    $viewer_data['version']     = $data['version'] ?? '';
    $viewer_data['resources']   = $data['resources'] ?? [];
    $viewer_data['max_players'] = (int)($data['sv_maxclients'] ?? $data['sv_maxClients'] ?? 0);
    $viewer_data['num_players'] = (int)($data['clients'] ?? 0);
    $viewer_data['tags']        = $data['vars']['tags'] ?? '';
    $viewer_data['mod_folder']  = $viewer_data['gametype'] ?: 'fivem';

    // Nom du jeu, protocole et état
    $viewer_data['nom_jeux']      = 'FiveM';
    $viewer_data['server_type']   = 'd';
    $viewer_data['protocol']      = 'fivem-json';
    $viewer_data['online']        = true;
    $viewer_data['players_list']  = $viewer_data['num_players'] > 0;
    $viewer_data['prive_public']  = '🌍 Public';

    // Détection de l'OS distant via la variable "sv_os"
    $vars = $data['vars'] ?? [];
    $os_var = strtolower($vars['sv_os'] ?? 'unknown');

    $viewer_data['systeme_exploitation'] = match ($os_var) {
        'windows' => 'w',
        'linux'   => 'l',
        default   => '?',
    };

    $viewer_data['serveur_type'] = 'Dedicated server';
    $viewer_data['serveur_os'] = match ($viewer_data['systeme_exploitation']) {
        'w' => '🪟 Windows',
        'l' => '🐧 Linux',
        default => 'Unknown',
    };

    // Liste des joueurs
    $players_url = "http://$ip:$port/players.json";
    $players_raw = @file_get_contents($players_url);
    $players_list = json_decode($players_raw, true) ?? [];

    $viewer_data['players'] = [];
    foreach ($players_list as $player) {
        $viewer_data['players'][] = [
            'name'  => $player['name'] ?? 'Unknown',
            'score' => $player['score'] ?? 0,
            'ping'  => $player['ping'] ?? 0,
        ];
    }

    return $viewer_data;
}