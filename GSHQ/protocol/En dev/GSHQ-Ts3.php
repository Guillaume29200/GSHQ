<?php
// Protocol pour Teamspeak 3
// Version : alpha 1.0 En developpement non fonctionnel
// Date de dev : 03/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_ts3_engine(array $server, array $viewer_data): array {
    $ip       = $server['adresse_ip'];
    $port     = (int)($server['gserver_port']);
    $qport    = (int)($server['gserver_qport'] ?? 10011);
    $timeout  = 2;

    $sock = @fsockopen($ip, $qport, $errno, $errstr, $timeout);
    if (!$sock) {
        $viewer_data['hostname'] = "❌ Impossible de se connecter au port query";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }

    stream_set_timeout($sock, $timeout);
    fgets($sock); // skip welcome

    $send = function($cmd) use ($sock) {
        fwrite($sock, $cmd . "\n");
        $response = '';
        while (true) {
            $line = fgets($sock);
            if ($line === false || str_starts_with($line, 'error')) {
                $response .= $line;
                break;
            }
            $response .= $line;
        }
        return $response;
    };

    $resp = $send("use port=$port");
    if (!str_contains($resp, 'error id=0')) {
        $viewer_data['hostname'] = "❌ Erreur lors de la sélection du serveur TS3";
        fclose($sock);
        return $viewer_data;
    }

    $resp_info    = $send("serverinfo");
    $resp_clients = $send("clientlist");
    fclose($sock);

    // Parse de façon sûre
    parse_str(str_replace([' ', "\n"], ['&', ''], trim($resp_info)), $info);

    $clients = [];
    foreach (explode('|', trim($resp_clients)) as $entry) {
        parse_str(str_replace(' ', '&', $entry), $data);
        if (($data['client_type'] ?? 1) == 0) {
            $clients[] = [
                'name' => $data['client_nickname'] ?? 'Inconnu',
                'ping' => $data['client_ping'] ?? 0,
                'id'   => $data['clid'] ?? null,
            ];
        }
    }

    // Sécurisation des accès aux clés
    $hostname 		= urldecode($info['virtualserver_name'] ?? 'Serveur inconnu');
    $clients_online = (int)($info['virtualserver_clientsonline'] ?? 0);
    $query_clients  = (int)($info['virtualserver_queryclientsonline'] ?? 0);
    $max_clients    = (int)($info['virtualserver_maxclients'] ?? 0);
    $is_private     = (int)($info['virtualserver_password'] ?? 0);

    // Traitement final
    $viewer_data['hostname']      = $hostname;
    $viewer_data['num_players']   = max(0, $clients_online - $query_clients);
    $viewer_data['max_players']   = $max_clients;
    $viewer_data['mod_folder']    = 'teamspeak3';
    $viewer_data['nom_jeux']      = 'TeamSpeak 3';
    $viewer_data['players_list']  = $clients;
    $viewer_data['online']        = true;
    $viewer_data['debug_timeout'] = 'Non';
    $viewer_data['prive_public']  = ($is_private === 1) ? '🔒 Privé (mot de passe)' : '🌍 Public';
    $viewer_data['serveur_type']  = '🗣 Serveur vocal TS3';

    return $viewer_data;
}