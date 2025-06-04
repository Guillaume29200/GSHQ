<?php
require_once(__DIR__ . '/header.php');

// Sécurisation de l'ID via GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
	header('Location: logout.php');
	exit;
}

// Vérification existence du serveur
$stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_gameserver WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->fetchColumn() == 0) {
	header('Location: logout.php');
	exit;
}

// Suppression
$stmt = $conn->prepare("DELETE FROM tbl_gameserver WHERE id = ?");
$stmt->execute([$id]);

header('Location: gameserver-list.php');
exit;
