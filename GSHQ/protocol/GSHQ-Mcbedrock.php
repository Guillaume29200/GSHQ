<?php
// Protocol Minecraft Bedrock Edition (RakNet ping)
// Compatible avec : Minecraft Bedrock Edition (Windows 10 / Mobile / Console)
// Version : alpha 1.1
// Date : 10/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_mcbedrock_engine(array $server, array $viewer_data): array {
    $ip = $server['adresse_ip'] ?? '';
    $port = (int)($server['gserver_port'] ?? 19132);

    if (!$ip || !$port) {
        error_log("GSHQ_mcbedrock_engine: adresse IP ou port manquant");
        return $viewer_data;
    }

    $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        error_log("GSHQ_mcbedrock_engine: échec création socket");
        return $viewer_data;
    }

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);

    // ⛳️ Paquet identique à LGSL7
    $packet = "\x01"
            . str_repeat("\x00", 8)
            . "\x00\xff\xff\x00"
            . "\xfe\xfe\xfe\xfe"
            . "\xfd\xfd\xfd\xfd"
            . "\x12\x34\x56\x78"
            . "LGSLLIST";

    if (@socket_sendto($socket, $packet, strlen($packet), 0, $ip, $port) === false) {
        error_log("GSHQ_mcbedrock_engine: échec socket_sendto");
        socket_close($socket);
        return $viewer_data;
    }

    $response = '';
    $from = '';
    $port_recv = 0;
    $recv_len = @socket_recvfrom($socket, $response, 4096, 0, $from, $port_recv);

    socket_close($socket);

    if ($recv_len === false || strlen($response) < 35) {
        error_log("GSHQ_mcbedrock_engine: aucune réponse ou réponse trop courte");
        return $viewer_data;
    }

    $data = substr($response, 35);
    $fields = explode(';', $data);

    if (count($fields) < 6) {
        error_log("GSHQ_mcbedrock_engine: réponse mal formée");
        return $viewer_data;
    }

    // 🎨 Fonction pour convertir les couleurs § en HTML
    $motd_raw = $fields[1] ?? 'Serveur sans nom';
    $motd_html = preg_replace_callback('/§[0-9a-fk-or]/i', function ($matches) {
        static $colors = [
            '0' => '#000000', '1' => '#0000AA', '2' => '#00AA00', '3' => '#00AAAA',
            '4' => '#AA0000', '5' => '#AA00AA', '6' => '#FFAA00', '7' => '#AAAAAA',
            '8' => '#555555', '9' => '#5555FF', 'a' => '#55FF55', 'b' => '#55FFFF',
            'c' => '#FF5555', 'd' => '#FF55FF', 'e' => '#FFFF55', 'f' => '#FFFFFF',
        ];
        $code = strtolower(substr($matches[0], 1));
        return isset($colors[$code]) ? "<span style=\"color:{$colors[$code]}\">" : '';
    }, $motd_raw) . str_repeat('</span>', substr_count($motd_raw, '§'));

    $viewer_data['hostname']      = strip_tags($motd_html);
    $viewer_data['motd']          = $motd_html;
	$viewer_data['version'] = $fields[3] ?? null;
	$viewer_data['protocol'] = $fields[2] ?? null;
    $viewer_data['num_players']   = (int)($fields[4] ?? 0);
    $viewer_data['max_players']   = (int)($fields[5] ?? 0);
    $viewer_data['map']           = $fields[7] ?? 'Unknown';
    $viewer_data['mode']          = $fields[8] ?? null;
    $viewer_data['edition']       = $fields[0] ?? 'MCPE';
    $viewer_data['players']       = [];
    $viewer_data['online']        = true;
    $viewer_data['debug_response_ascii'] = $data;

    return $viewer_data;
}
