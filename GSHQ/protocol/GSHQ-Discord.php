<?php
// Protocol Discord API (via invite code)
// Version : alpha 1.1
// Date : 04/06/2025
// https://gameserver-hub.com/
// https://esport-cms.net
// Auteur : Slymer
// Discord : .slymer
// Email : hooxie@live.fr

function GSHQ_discord_engine(array $server, array $viewer_data): array {
    $invite_code = $server['adresse_ip']; // stocke le code invite Discord ici, ex: "abcd1234"

    $api_url = "https://discord.com/api/v9/invites/{$invite_code}?with_counts=true";

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: GameServerHub/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($api_url, false, $context);
    if (!$json) {
        // Pas de réponse
        $viewer_data['online'] = false;
        $viewer_data['error'] = "Impossible de récupérer les données Discord.";
        return $viewer_data;
    }

    $data = json_decode($json, true);
    if (!$data || isset($data['message'])) {
        $viewer_data['online'] = false;
        $viewer_data['error'] = $data['message'] ?? "Réponse invalide de Discord.";
        return $viewer_data;
    }

    // Remplir $viewer_data à la manière JK2, sans HTML
    $guild = $data['guild'] ?? [];

    $viewer_data['online'] = true;
    $viewer_data['hostname'] = $guild['name'] ?? 'N/A';
    $viewer_data['guild_id'] = $guild['id'] ?? null;
    $viewer_data['description'] = $guild['description'] ?? '';
    $viewer_data['max_players'] = $data['approximate_member_count'] ?? 0;
    $viewer_data['num_players'] = $data['approximate_presence_count'] ?? 0;
    $viewer_data['premium_subscription_count'] = $guild['premium_subscription_count'] ?? 0;
    $viewer_data['nsfw'] = $guild['nsfw'] ?? false;
    $viewer_data['features'] = $guild['features'] ?? [];
    $viewer_data['vanity_url_code'] = $data['code'] ?? '';
    $viewer_data['inviter'] = $data['inviter']['username'] ?? '';
	$viewer_data['protocol'] = 'discord';

    // Pas de players list car Discord ne le donne pas comme un serveur de jeu

    // Optionnel : infos du welcome screen, canaux d'accueil
    if (isset($guild['welcome_screen']['welcome_channels'])) {
        $channels = [];
        foreach ($guild['welcome_screen']['welcome_channels'] as $ch) {
            $channels[] = [
                'channel_id' 	=> $ch['channel_id'] ?? '',
                'description' 	=> $ch['description'] ?? '',
                'emoji_name' 	=> $ch['emoji_name'] ?? '',
            ];
        }
        $viewer_data['welcome_channels'] = $channels;
    }

    return $viewer_data;
}