<?php
/**
 * Version 1.0 by GameServer-Hub
 * https://gameserver-hub.com/
 * https://esport-cms.net
 * Auteur : Slymer
 * Discord : .slymer
 * Email : hooxie@live.fr
 */
// On inclue la matrice de gestion des données
require_once __DIR__ . '/GSHQ-Matrice.php';

function get_viewer_data(array $server): array {
    $viewer_id = $server['viewer_id'] ?? 0;
    $protocol = strtolower($server['protocol'] ?? 'unknown');

    $viewer_data = [
        'hostname'        		=> '',
        'Adresse IP'      		=> "{$server['adresse_ip']}",
		'Port de connexion'     => "{$server['gserver_port']}",
		'Port Query'      		=> "{$server['gserver_qport']}",
        'num_players'     		=> 0,
        'max_players'     		=> 0,
        'map'             		=> '',
        'players'         		=> [],
        'online'          		=> false,
        'viewer_id'       		=> $viewer_id,
        'gshq_type'      		=> 'inconnu',
        'gshq_file_used' 		=> 'aucun'
    ];	

    $gshq_file = __DIR__ . "/../protocol/GSHQ-" . ucfirst($protocol) . ".php";
    $gshq_function = "GSHQ_{$protocol}_engine";

	if (file_exists($gshq_file)) {
		require_once $gshq_file;
		$viewer_data['gshq_file_used'] = basename($gshq_file);

		if (function_exists($gshq_function)) {
			$viewer_data['gshq_type'] = $protocol;
			$viewer_data = $gshq_function($server, $viewer_data);

			// On s'assure que la clé protocol est bien renseignée dans viewer_data
			$viewer_data['protocol'] = $protocol;

			return $viewer_data;
		} else {
			$viewer_data['gshq_type'] = 'fonction absente';
		}
	} else {
        $viewer_data['gshq_file_used'] = 'fichier introuvable';
    }

    return $viewer_data;
}