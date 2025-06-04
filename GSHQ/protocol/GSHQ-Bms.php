<?php
// Protocol Black Mesa - moteur Half-life 2 "Source"
// Version : alpha 1
// Date de dev : 31/05/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_bms_engine(array $server, array $viewer_data): array {
    $ip   = $server['adresse_ip'];
    $port = (int)$server['gserver_port'];

    $base_packet = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";

    $send_packet = function($ip, $port, $packet) {
        $sock = fsockopen("udp://$ip", $port, $errno, $errstr, 1);
        if (!$sock) return false;
        socket_set_timeout($sock, 1);
        fwrite($sock, $packet);
        $response = fread($sock, 4096);
        fclose($sock);
        return $response;
    };

    $response = $send_packet($ip, $port, $base_packet);

    if (!$response || strlen($response) < 3) {
        $viewer_data['hostname'] = "Pas de réponse ou réponse trop courte";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    $type = ord($response[4]);

    // Si c’est un challenge (0x41), on reconstruit la requête avec le token
    if ($type === 0x41) {
        $challenge = substr($response, 5, 4); // 4 bytes challenge token
        $response = $send_packet($ip, $port, $base_packet . $challenge);

        if (!$response || strlen($response) < 5) {
            $viewer_data['hostname'] = "Pas de réponse au challenge";
            $viewer_data['debug_timeout'] = 'Oui';
            return $viewer_data;
        }

        $type = ord($response[4]);
    }

    // Debug infos
    $viewer_data['debug_response_hex']   = strtoupper(bin2hex($response));
    $viewer_data['debug_response_ascii'] = preg_replace('/[^(\x20-\x7F)]*/','', $response);
    $viewer_data['debug_timeout']        = 'Non';

    if ($type !== 0x49) {
        $viewer_data['hostname'] = "Réponse inattendue : 0x" . strtoupper(dechex($type));
        return $viewer_data;
    }

    // Parse réponse 0x49
    $offset = 5;

    $readString = function($data, &$offset) {
        $end = strpos($data, "\x00", $offset);
        if ($end === false) return '';
        $str = substr($data, $offset, $end - $offset);
        $offset = $end + 1;
        return $str;
    };

	$protocol      = ord($response[$offset++]);                   // Version du protocole (ex: 17)
	$hostname      = $readString($response, $offset);             // Nom du serveur
	$map           = $readString($response, $offset);             // Nom de la carte actuelle (ex: de_dust2)
	$folder        = $readString($response, $offset);             // Nom du dossier du jeu (ex: cstrike)
	$game          = $readString($response, $offset);             // Nom du jeu (ex: Counter-Strike)
	$id            = unpack('v', substr($response, $offset, 2))[1]; $offset += 2; // ID Steam App (ex: 10 pour CS 1.6)
	$players       = ord($response[$offset++] ?? "\x00");         // Nombre de joueurs actuellement connectés
	$max_players   = ord($response[$offset++] ?? "\x00");         // Nombre maximum de joueurs
	$bots          = ord($response[$offset++] ?? "\x00");         // Nombre de bots présents
	$server_type   = $response[$offset++] ?? '';                  // Type de serveur : 'd' = dedicated, 'l' = listen, 'p' = SourceTV
	$environment   = $response[$offset++] ?? '';                  // OS : 'l' = Linux, 'w' = Windows, 'm' ou 'o' = Mac
	$visibility    = ord($response[$offset++] ?? "\x00");         // Visibilité : 0 = public, 1 = privé (mot de passe requis)
	$vac           = ord($response[$offset++] ?? "\x00");         // VAC : 0 = non protégé, 1 = protégé par VAC

	// Assignation des données brutes
	$viewer_data['hostname']       			= $hostname;
	$viewer_data['map']            			= $map;
	$viewer_data['mod_folder']     			= $folder;
	$viewer_data['nom_jeux']       			= $game;
	$viewer_data['num_players']    			= $players;
	$viewer_data['max_players']    			= $max_players;
	$viewer_data['bots']           			= $bots;
	$viewer_data['server_type']    			= $server_type;
	$viewer_data['systeme_exploitation']    = $environment;
	$viewer_data['prive_public']     		= $visibility;
	$viewer_data['vac']            			= $vac;
	$viewer_data['steam_app_id']   			= $id;
	$viewer_data['protocol']       			= $protocol;
	$viewer_data['online']         			= true;

	// Traductions pour les utilisateurs
	$viewer_data['vac_human']         = ($vac === 1); // true si VAC activé
	$viewer_data['anti_cheat']        = ($vac === 1) ? '✅ Protégé par VAC' : '❌ Non protégé VAC';
	$viewer_data['prive_public']  	  = ($visibility === 1) ? '🔒 Privé (mot de passe)' : '🌍 Public';
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