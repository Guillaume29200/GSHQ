<?php
require_once __DIR__ . '/admin/Configuration/config.php';
require_once __DIR__ . '/GSHQ/Controllers/Autoload.php';

try {
    $stmt = $conn->query("SELECT * FROM tbl_gameserver ORDER BY id ASC");
    $gameservers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= htmlspecialchars(WEBSITE_NAME) ?></title>
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
				<div class="card shadow-sm">
					<div class="card-header d-flex justify-content-between align-items-center bg-dark text-light">
						<h5 class="mb-0">👥 Game & Voice Servers</h5>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-bordered table-striped">
								<thead class="table-dark">
									<tr>
										<th></th>
										<th><center>🖥️ Hostname</center></th>
										<th><center>🌐 IP Address</center></th>
										<th><center>👥 Players</center></th>
										<th><center>🗺️ Map</center></th>
										<th><center>📡 Status</center></th>
										<th><center>⚡ details</center></th>
									</tr>
								</thead>
								<tbody>
									<?php if (!empty($gameservers)) : ?>
										<?php foreach ($gameservers as $servers) : ?>
											<?php
												// Préparation des données pour GSHQ
												$server_data = [
													'adresse_ip'    => $servers['adress_ip'],
													'gserver_port'  => $servers['c_port'],
													'gserver_qport' => $servers['q_port'] ?: $servers['c_port'],
													'jeux'          => $servers['games'],
													'protocol'      => $servers['games'],
													'viewer_id'     => $servers['id'],
												];
												$viewer_data = get_viewer_data($server_data);
											?>
											<tr>
												<td><center><img src="<?= BASE_URI ?>/GSHQ/uploads/games/<?= htmlspecialchars($servers['games']) ?>.png" width="48" height="48"></center></td>
												<td><center><?= htmlspecialchars($viewer_data['hostname'] ?? 'Pas de réponse') ?></center></td>
												<td><center><?= htmlspecialchars($servers['adress_ip']) ?>:<?= htmlspecialchars($servers['c_port']) ?></center></td>
												<td><center><?= $viewer_data['num_players'] ?? 0 ?>/<?= $viewer_data['max_players'] ?? 0 ?></center></td>
												<td><center><?= htmlspecialchars($viewer_data['map'] ?? '-') ?></center></td>
												<td><center><?= !empty($viewer_data['online']) ? '✅ Online' : '❌ Offline' ?></center></td>
												<td><center><a href="gameserver-details.php?id=<?= urlencode($servers['id']) ?>" class="btn btn-sm btn-primary">🔍 View</a></center></td>
											</tr>
										<?php endforeach; ?>
									<?php else : ?>
										<tr>
											<td colspan="7" class="text-center">No servers available.</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
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
					<p class="mb-1">© 2025 <?= htmlspecialchars(WEBSITE_NAME) ?>. All rights reserved.</p>
					<p class="mb-0">Made with ❤️ by <a href="https://gameserver-hub.com" target="_blank" rel="noopener noreferrer" class="text-light fw-bold">GameServer-Hub</a></p>
				</div>
			</div>
		</footer>
	</body>
</html>