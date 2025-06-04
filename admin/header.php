<?php
require_once __DIR__ . '/Configuration/config.php';
require_once __DIR__ . '/Configuration/session.php';
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= htmlspecialchars(WEBSITE_NAME) ?> - Administration</title>
		<meta name="description" content="<?= htmlspecialchars(WEBSITE_DESC) ?>">
		<meta name="keywords" content="<?= htmlspecialchars(WEBSITE_KEYWORDS) ?>">
		<meta name="author" content="gameserver-hub.com">
		<link rel="icon" href="<?= WEBSITE_FAVICON ?>" type="image/x-icon">
		<link href="<?= BASE_URI ?>/admin/assets/bootstrap.min.css" rel="stylesheet">
		<link href="<?= BASE_URI ?>/admin/assets/offcanvas-navbar.css" rel="stylesheet">
	</head>
	<body class="bg-body-tertiary">
		<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark" aria-label="Main navigation">
			<div class="container-fluid">
				<a class="navbar-brand" href="<?= BASE_URI ?>/admin/"><?= htmlspecialchars(WEBSITE_NAME) ?> - Admin</a>
				<button class="navbar-toggler p-0 border-0" type="button" id="navbarSideCollapse" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="navbar-collapse offcanvas-collapse" id="navbarsExampleDefault">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<li class="nav-item"><a class="nav-link" target="_blank" href="<?= BASE_URI ?>/">🌍 View Website</a></li>
						<li class="nav-item"><a class="nav-link" target="_blank" href="https://esport-cms.net/forum">💬 eSport-CMS Forum</a></li>
						<li class="nav-item"><a class="nav-link" href="changelog.php">🚀 GSHQ Changelog</a></li>
						<li class="nav-item"><a class="nav-link" href="logout.php">🚪 Log Out</a></li>
					</ul>
				</div>
			</div>
		</nav>
		<div class="nav-scroller bg-body shadow-sm">
			<nav class="nav" aria-label="Secondary navigation">
				<a class="nav-link" href="<?= BASE_URI ?>/admin/">🏠 Dashboard</a>
				<a class="nav-link" href="<?= BASE_URI ?>/admin/users-list.php">👥 Manage my users</a>
				<a class="nav-link" href="<?= BASE_URI ?>/admin/gameserver-list.php">🎮 Manage my Game & Voice Servers</a>
			</nav>
		</div>
		<main class="container">