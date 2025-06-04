<?php
// Protocol pour Arma 3
// Version : alpha 1.0
// Date de dev : 03/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_arma3_engine(array $server, array $viewer_data): array
{
    /* ---------- 1. PRÉPARATION ---------- */
    $ip         = $server['adresse_ip'];
    $port    	= (int) $server['gserver_port'];
    $qport   	= (int) ($server['gserver_qport'] ?? $port + 1);   // 2302 → 2303
    $timeout    = 2;

    $sock = @fsockopen("udp://$ip", $qport, $errno, $errstr, $timeout);
    if (!$sock) {
        $viewer_data['hostname']     = "❌ Port query injoignable";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }
    stream_set_timeout($sock, $timeout);

    /* ---------- 2. FONCTIONS UTILES ---------- */
    $send = function ($packet) use ($sock) {
        fwrite($sock, $packet);
        return fread($sock, 4096);
    };
    $readString = function ($data, &$o) {
        $end = strpos($data, "\x00", $o);
        if ($end === false) return '';
        $str = substr($data, $o, $end - $o);
        $o   = $end + 1;
        return $str;
    };

    /* ---------- 3. A2S_INFO ---------- */
    $a2sInfo = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";
    $resp    = $send($a2sInfo);
    if (!$resp) {
        fclose($sock);
        $viewer_data['hostname']     = "❌ Pas de réponse A2S_INFO";
        $viewer_data['debug_timeout'] = 'Oui';
        return $viewer_data;
    }
    // challenge ?
    if (ord($resp[4]) === 0x41) {
        $challenge = substr($resp, 5, 4);
        $resp      = $send($a2sInfo . $challenge);
    }

    /* ---------- 3.1 PARSE INFO ---------- */
    $o = 5;                                 // on saute “I” (0x49)
    $protocol       = ord($resp[$o++]);
    $hostname       = $readString($resp, $o);
    $map            = $readString($resp, $o);          // mission: “Altis”, “Malden…”
    $folder         = $readString($resp, $o);          // toujours “arma3”
    $game           = $readString($resp, $o);          // “Arma 3”
    $steamAppId     = unpack('v', substr($resp, $o, 2))[1]; $o += 2;
    $players        = ord($resp[$o++]);
    $maxPlayers     = ord($resp[$o++]);
    $bots           = ord($resp[$o++]);
    $srvType        = $resp[$o++];
    $os             = $resp[$o++];
    $visibility     = ord($resp[$o++]);
    $vac            = ord($resp[$o++]);
    $version        = $readString($resp, $o);          // ex : “2.14.150957”

    /* ----- Extra Data Flags ----- */
    $edf = ord($resp[$o++] ?? "\x00");
    $edfPort   = ($edf & 0x80) ? unpack('v', substr($resp, $o, 2))[1] : $port;
    if ($edf & 0x80) $o += 2;
    if ($edf & 0x10) $o += 8;                          // steamID ignoré ici
    if ($edf & 0x40) $o += 2 + strlen($readString($resp, $o)); // TV port + name
    $keywords       = ($edf & 0x20) ? $readString($resp, $o) : '';

    /* ---------- 4. A2S_PLAYER (optionnel) ---------- */
    $playerList = [];
    $a2sPlayers = "\xFF\xFF\xFF\xFFU\xFF\xFF\xFF\xFF";
    $pr = $send($a2sPlayers);
    if ($pr && ord($pr[4]) === 0x41) {                  // challenge
        $challenge = substr($pr, 5, 4);
        $pr        = $send("\xFF\xFF\xFF\xFFU" . $challenge);
    }
    if ($pr && ord($pr[4]) === 0x44) {
        $o = 5;
        $count = ord($pr[$o++]);
        for ($i = 0; $i < $count; $i++) {
            $o++;                                      // index
            $name = $readString($pr, $o);
            $score = unpack('l', substr($pr, $o, 4))[1]; $o += 4;
            $duration = unpack('f', substr($pr, $o, 4))[1]; $o += 4;
            $playerList[] = [
                'name'      => $name,
                'score'     => $score,
                'duration'  => round($duration, 1) . ' s'
            ];
        }
    }

    fclose($sock);

    /* ---------- 5. PARSE DES GAMETAGS ---------- */
    $tags = [];
    foreach (explode(':', $keywords) as $tag) {
        $key = substr($tag, 0, 1);
        $val = substr($tag, 1);
        $tags[$key] = $val;
    }
    $battlEye    = ($tags['B'] ?? '0') === '1';
    $difficulty  = $tags['H'] ?? ($tags['I'] ?? 'N/A');      // H=Hardcore, I=Regular selon versions
    $dedicated   = ($tags['D'] ?? '1') === '1';

    /* ---------- 6. REMPLISSAGE viewer_data ---------- */
    $viewer_data = array_merge($viewer_data, [
        'hostname'             => $hostname,
        'map'                  => $map,
        'mod_folder'           => $folder,
        'nom_jeux'             => $game,
        'num_players'          => $players,
        'max_players'          => $maxPlayers,
        'bots'                 => $bots,
        'server_type'          => $srvType,
        'systeme_exploitation' => $os,
        'prive_public'         => $visibility,
        'vac'                  => $vac,
        'steam_app_id'         => $steamAppId,
        'protocol'             => $protocol,
        'version'              => $version,
        'query_port'           => $edfPort,
        'keywords'             => $keywords,
        'players_list'         => $playerList,
        'online'               => true,

        // Traductions « user-friendly »
        'anti_cheat'           => $battlEye ? '✅ BattlEye ON' : '❌ BattlEye OFF',
        'prive_public'   	   => $visibility ? '🔒 Privé' : '🌍 Public',
        'serveur_type'   	   => $dedicated ? '🖥 Dédié' : '👥 Listen',
        'serveur_os'           => $os === 'l' ? '🐧 Linux' : ($os === 'w' ? '🪟 Windows' : '🍎 macOS'),
        'difficulte'           => $difficulty
    ]);

    return $viewer_data;
}