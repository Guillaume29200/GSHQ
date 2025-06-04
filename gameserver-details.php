<?php
require_once __DIR__ . '/admin/Configuration/config.php';
require_once __DIR__ . '/GSHQ/Controllers/Autoload.php';

// Vérifie qu'un ID est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URI . "/index.php");
    exit;
}

$server_id = (int) $_GET['id'];

// Récupère le serveur depuis la base
try {
    $stmt = $conn->prepare("SELECT * FROM tbl_gameserver WHERE id = ?");
    $stmt->execute([$server_id]);
    $server = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$server) {
        header("Location: " . BASE_URI . "/index.php");
        exit;
    }
} catch (PDOException $e) {
    // En cas d'erreur SQL, redirige aussi
    header("Location: " . BASE_URI . "/index.php");
    exit;
}

// Préparation des données pour GSHQ
$server_data = [
    'adresse_ip'    => $server['adress_ip'],
    'gserver_port'  => $server['c_port'],
    'gserver_qport' => $server['q_port'] ?: $server['c_port'],
    'jeux'          => $server['games'],
    'protocol'      => $server['games'],
    'viewer_id'     => $server['id'],
];

// Appel de GSHQ
$viewer_data = get_viewer_data($server_data);
include __DIR__ . '/GSHQ/Controllers/GSHQ-MapViewers.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= htmlspecialchars(WEBSITE_NAME) ?> - Game Servers</title>
		<meta name="description" content="<?= htmlspecialchars(WEBSITE_DESC) ?>">
		<meta name="keywords" content="<?= htmlspecialchars(WEBSITE_KEYWORDS) ?>">
		<meta name="author" content="gameserver-hub.com">
		<link rel="icon" href="<?= WEBSITE_FAVICON ?>" type="image/x-icon">
		<link href="<?= BASE_URI ?>/admin/assets/bootstrap.min.css" rel="stylesheet">
	</head>
	<body class="bg-light text-dark">
		<!-- Header -->
		<header class="bg-dark text-light py-3">
			<div class="container">
				<h1 class="mb-0">🌐 <?= WEBSITE_NAME ?></h1>
				<p class="mb-0"><?= WEBSITE_SLOGAN ?></p>
			</div>
		</header>
		<!-- Main -->
		<main class="container my-4">
			<div class="col-md-12">
				<div class="card shadow-lg mb-4">
					<div class="card-header bg-dark text-white d-flex align-items-center">
						<h5 class="mb-0 me-auto">🔍 Server Details #<?= htmlspecialchars($server['id']) ?></h5>
						<span class="badge bg-secondary"><?= strtoupper(htmlspecialchars($server['games'])) ?></span>
					</div>
					<div class="card-body">
						<div class="row g-4 align-items-center">
							<!-- Colonne gauche : Image du jeu -->
							<div class="col-md-2 text-center">
								<img src="<?= BASE_URI ?>/GSHQ/uploads/games/<?= htmlspecialchars($server['games']) ?>.png" width="256" height="256" alt="Jeu" class="img-fluid rounded">
								<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'favicon_base64')): ?>
									<div class="mt-2">
										<img src="<?= $viewer_data['favicon_base64'] ?? '' ?>" alt="Favicon" class="img-fluid" style="max-height: 48px;">
									</div>
								<?php endif; ?>
							</div>
							<!-- Colonne droite : Détails -->
							<div class="col-md-7">
								<div class="row">

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'hostname')): ?>
										<div class="col-md-6 mb-2">
											<strong>🖥️ Hostname :</strong> <?= htmlspecialchars($viewer_data['hostname'] ?? 'Pas de réponse') ?>
										</div>
									<?php endif; ?>
										<div class="col-md-6 mb-2">
											<strong>🖥️ IP adress :</strong> <?= htmlspecialchars($server['adress_ip']) ?>:<?= htmlspecialchars($server['c_port']) ?>
										</div>									

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'map')): ?>
										<div class="col-md-6 mb-2">
											<strong>🗺️ Map :</strong> <?= htmlspecialchars($viewer_data['map'] ?? '-') ?>
										</div>
									<?php endif; ?>

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'num_players') && protocol_supports_field($viewer_data['protocol'] ?? '', 'max_players')): ?>
										<div class="col-md-6 mb-2">
											<strong>👥 Players :</strong> <?= $viewer_data['num_players'] ?? 0 ?>/<?= $viewer_data['max_players'] ?? 0 ?>
										</div>
									<?php endif; ?>

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'bots')): ?>
										<div class="col-md-6 mb-2">
											<strong>🤖 Bots :</strong> <?= $viewer_data['bots'] ?? 0 ?>
										</div>
									<?php endif; ?>

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'anti_cheat')): ?>
										<div class="col-md-6 mb-2">
											<strong>🛡️ VAC :</strong> <?= $viewer_data['anti_cheat'] ?? 'Inconnu' ?>
										</div>
									<?php endif; ?>

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'prive_public')): ?>
										<div class="col-md-6 mb-2">
											<strong>🔒 Visibility :</strong> <?= $viewer_data['prive_public'] ?? 'Inconnue' ?>
										</div>
									<?php endif; ?>

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'serveur_type')): ?>
										<div class="col-md-6 mb-2">
											<strong>📦 Server Type :</strong> <?= htmlspecialchars($viewer_data['serveur_type'] ?? 'Inconnu') ?>
										</div>
									<?php endif; ?>

									<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'serveur_os')): ?>
										<div class="col-md-6 mb-2">
											<strong>💻 Operating System :</strong> <?= htmlspecialchars($viewer_data['serveur_os'] ?? 'Inconnu') ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
							<!-- Map image -->
							<div class="col-md-3">
								<div class="card h-100 shadow-sm">
									<img src="<?= $map_to_show; ?>" class="img-fluid rounded shadow">
								</div>
							</div>
							<div class="col-md-12">
								<?php if (protocol_supports_field($viewer_data['protocol'] ?? '', 'players_list')): ?>
									<?php if (!empty($viewer_data['players'])): ?>
										<h4>👥 Connected Players (<?php echo $viewer_data['num_players']; ?>/<?php echo $viewer_data['max_players']; ?>)</h4>
										<table class="table table-striped table-bordered">
											<thead class="thead-dark">
												<tr>
													<th>#</th>
													<th>Pseudo</th>
													<th>Score</th>
													<th>Ping</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($viewer_data['players'] as $index => $player): ?>
													<tr>
														<td><?php echo $index + 1; ?></td>
														<td><?php echo htmlspecialchars($player['name'], ENT_QUOTES); ?></td>
														<td><?php echo $player['score']; ?></td>
														<td><?php echo $player['ping']; ?> ms</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									<?php else: ?>
										<div class="alert alert-info"><center>No players currently connected.</center></div>
									<?php endif; ?>
								<?php endif; ?>
							</div>							
						</div>
						<hr class="mt-4">
						<div class="text-end">
							<a href="index.php" class="btn btn-outline-secondary">⬅ Back to the server list</a>
						</div>
					</div>
				</div>
			</div>
		</main>
		<!-- Footer -->
		<footer class="bg-dark text-light py-4 mt-5">
			<div class="container">
				<div class="row gy-4">
					<div class="col-md-5">
						<h5 class="fw-bold mb-3">🎮 <?= htmlspecialchars(WEBSITE_NAME) ?></h5>
						<p><?= htmlspecialchars(WEBSITE_DESC) ?></p>
					</div>
					<div class="col-md-4">
						<h5 class="fw-bold mb-3">📌 Useful Links</h5>
						<ul class="list-unstyled">
							<?php foreach ($footerLinks as $name => $url): ?>
								<li><a href="<?php echo $url; ?>" target="_blank" class="text-light text-decoration-none"><?php echo $name; ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="col-md-3 text-md-end">
						<h5 class="fw-bold mb-3">🔗 Quick Access</h5>
						<ul class="list-unstyled">
							<li><a href="<?= BASE_URI ?>/admin/" class="text-light text-decoration-none">🔐 Admin Panel</a></li>
						</ul>
					</div>
				</div>
				<hr class="border-secondary my-4">
				<div class="text-center small">
					<p class="mb-1">© 2025 GameServer-Hub Query. All rights reserved.</p>
					<p class="mb-0">Made with ❤️ by <a href="https://gameserver-hub.com" target="_blank" rel="noopener noreferrer" class="text-light fw-bold">GameServer-Hub</a></p>
				</div>
			</div>
		</footer>
	</body>
</html>