<?php
session_start();

// Vérifie si l'utilisateur est déjà sur une page d'authentification
$current_page = basename($_SERVER['PHP_SELF']);
$auth_pages = ['login.php', 'logout.php'];

if (!isset($_SESSION['user_id']) && !in_array($current_page, $auth_pages)) {
    header("Location: login.php");
    exit;
}

// Vérification des permissions (optionnel, à adapter selon les besoins)
if (isset($_SESSION['user_rang']) && $_SESSION['user_rang'] !== 'admin' && !in_array($current_page, $auth_pages)) {
    die("Accès refusé. Vous n'avez pas les permissions nécessaires.");
}
?>