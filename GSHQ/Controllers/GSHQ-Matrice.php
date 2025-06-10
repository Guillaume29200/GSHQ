<?php
// Matrice d'affichage dynamique par protocole.
// Certains jeux ne retournent pas toutes les variables classiques (nom de la map, nombre de bots, etc.). 
// Cette matrice permet d'afficher uniquement les informations pertinentes pour chaque protocole de jeu.
// Version 1.1 — GameServer Hub
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function protocol_supports_field(string $protocol, string $field): bool {
    $support_matrix = [
        'default' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'protocol'],																		// Si aucun "support_matrix" n'est défini on lui donne un mod par "defaut"
		'mcbedrock' 	=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'motd', 'version', 'protocol'],																		// Minecraft Bedrock Edition
		'beammp' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'players_list', 'protocol'],																// Protocol BeamMP
		'jk2' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'players_list', 'protocol'],														// Protocol Star Wars Jedi Knight 2
		'mohaa' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'players_list', 'protocol'],														// Protocol Modal of honor Debarquement allié
		'wet' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'players_list', 'protocol'],
		'cod4' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'players_list', 'protocol' , 'serveur_os'],							// Protocol Call of duty 4 Modern Warfare
		'mcj' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'prive_public', 'version', 'favicon_base64', 'latency_ms'],								// Protocol Minecraft Java Edition
        'hl1' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol Half-life 1
        'hl2' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol Half-life 2
		'insurgency' 	=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol Insurgency
		'fof' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol Fistful of Frags
		'tf2' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol Team fortress 2
		'css' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'players_list', 'anti_cheat', 'serveur_os', 'protocol'],	// Protocol Counter-Strike Source
		'onset' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'players_list', 'anti_cheat', 'serveur_os', 'protocol'],	// Protocol Onset
		'cs2' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'players_list', 'anti_cheat', 'serveur_os', 'protocol'],	// Protocol Counter-Strike 2
		'csgo' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'players_list', 'anti_cheat', 'serveur_os', 'protocol'],	// Protocol Counter-Strike Global offensive
		'bl' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol BATTALION: Legacy
		'nmrh' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol No More Room in Hell
		'l4d' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'players_list', 'anti_cheat', 'serveur_os', 'protocol'],	// Protocol Left 4 Dead
		'l4d2' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'players_list', 'anti_cheat', 'serveur_os', 'protocol'],	// Protocol Left 4 Dead 2
		'hl2mp' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol Hafl-life 2 Deatchmatch
		'pvkii' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Pirates, Vikings and Knights II
		'valheim' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'protocol'],														// Protocol Valheim
		'ase' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'anti_cheat', 'serveur_os', 'protocol'],					// Protocol ARK Survival Evolved
		'7dtd' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'serveur_os', 'protocol'],											// Protocol 7 days to die
		'pz' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'anti_cheat', 'serveur_os', 'protocol'],							// Protocol Project Zomboid
		'bms' 			=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'serveur_os', 'protocol'],											// Protocol Black Mesa
		'arma3' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'anti_cheat', 'serveur_os', 'difficulte', 'version', 'protocol'],	// Protocol Arma 3
		//'palworld' 		=> ['hostname', 'ip_port', 'num_players', 'max_players', 'map', 'prive_public', 'bots', 'players_list', 'anti_cheat', 'serveur_os', 'protocol'],	// Protocol Palworld		
		'fivem' 		=> ['hostname', 'ip_port', 'gametype', 'mapname', 'max_players', 'num_players', 'serveur_type', 'serveur_os', 'players_list', 'players', 'tags', 'sv_maxClients', 'clients', 'protocol'], // Protocol FiveM
		'redm' 			=> ['hostname', 'ip_port', 'gametype', 'mapname', 'max_players', 'num_players', 'serveur_type', 'serveur_os', 'players_list', 'players', 'tags', 'sv_maxClients', 'clients', 'protocol'], // Protocol RedM
		// Partie serveur vocal
		'discord' 		=> ['hostname', 'vanity_url_code', 'max_players', 'num_players', 'premium_subscription_count', 'nsfw', 'features', 'inviter', 'welcome_channels'],	// Protocol Discord
		//'ts3' => ['hostname', 'ip_port', 'num_players', 'max_players', 'prive_public', 'version', 'uptime', 'platform'],		
    ];

    // Normalise le protocole en minuscules pour éviter les soucis
    $protocol = strtolower($protocol);

    $fields = $support_matrix[$protocol] ?? $support_matrix['default'];
    return in_array($field, $fields, true);
}