<?php
require_once(__DIR__ . '/header.php');

// Sécurisation de l'ID via GET
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$user_id) {
	header('Location: logout.php');
	exit;
}

// Empêche la suppression de l'utilisateur ID 1
if ($user_id == 1) {
    // Optionnel : tu peux afficher un message ou juste rediriger
    header('Location: users-list.php?error=cannot_delete_admin');
    exit;
}

// Vérification existence de l'utilisateur
$stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_users WHERE user_id = ?");
$stmt->execute([$user_id]);

if ($stmt->fetchColumn() == 0) {
	header('Location: logout.php');
	exit;
}

// Suppression
$stmt = $conn->prepare("DELETE FROM tbl_users WHERE user_id = ?");
$stmt->execute([$user_id]);

header('Location: users-list.php');
exit;
