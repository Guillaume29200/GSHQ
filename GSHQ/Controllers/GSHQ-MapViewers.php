<?php
// Fonction pour renommer proprement les maps
function sanitizeMapName($mapName) {
    return str_replace(['/', '\\'], '_', $mapName);
}

// Vérifie que $viewer_data existe bien
if (!isset($viewer_data) || !is_array($viewer_data)) {
    $map_to_show = BASE_URI . "/GSHQ/uploads/map/inconnu.jpg";
    return;
}

if (!isset($viewer_data['games']) && isset($server_data['jeux'])) {
    $viewer_data['games'] = $server_data['jeux'];
}

// Récupération des infos
$games = $viewer_data['games'] ?? 'inconnu';
$map_name = $viewer_data['map'] ?? 'inconnu';
$map_sanitized = sanitizeMapName($map_name);

// Base absolue (remonte d’un dossier depuis /GSHQ/Controllers)
$base_path = realpath(__DIR__ . '/../'); // = GSHQ/
$uploads_dir = $base_path . "/uploads/map";

// Cas spéciaux
if (in_array($games, ['fivem', 'redm', 'dst'])) {
    $file_path = "$uploads_dir/$games/world.jpg";
    $file_uri  = BASE_URI . "/GSHQ/uploads/map/$games/world.jpg";
} else {
    $file_path = "$uploads_dir/$games/{$map_sanitized}.jpg";
    $file_uri  = BASE_URI . "/GSHQ/uploads/map/$games/{$map_sanitized}.jpg";
}

// Valeur de fallback
$default_uri = BASE_URI . "/GSHQ/uploads/map/inconnu.jpg";

// Sélection finale
$map_to_show = (file_exists($file_path)) ? $file_uri : $default_uri;
?>