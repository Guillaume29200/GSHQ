<?php
require_once __DIR__ . '/Configuration/config.php';
require_once __DIR__ . '/Configuration/session.php';
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
<script src="https://getbootstrap.com/docs/5.3/assets/js/color-modes.js"></script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars(WEBSITE_NAME) ?> - Game Servers</title>
<meta name="description" content="<?= htmlspecialchars(WEBSITE_DESC) ?>">
<meta name="keywords" content="<?= htmlspecialchars(WEBSITE_KEYWORDS) ?>">
<meta name="author" content="gameserver-hub.com">
<link rel="icon" href="<?= WEBSITE_FAVICON ?>" type="image/x-icon">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
<link href="https://getbootstrap.com/docs/5.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://getbootstrap.com/docs/5.3/examples/offcanvas-navbar/offcanvas-navbar.css" rel="stylesheet">
<meta name="theme-color" content="#712cf9">
</head>
<body class="bg-body-tertiary">
	<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark" aria-label="Main navigation">
		<div class="container-fluid">
			<a class="navbar-brand" href="<?= BASE_URI ?>/admin/"><?= htmlspecialchars(WEBSITE_NAME) ?></a>
			<button class="navbar-toggler p-0 border-0" type="button" id="navbarSideCollapse" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
		</div>
	</nav>
	<main class="container">