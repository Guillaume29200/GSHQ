<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../GSHQ/Controllers/Autoload.php';

try {
    $stmt = $conn->query("SELECT * FROM tbl_gameserver ORDER BY id ASC");
    $gameservers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<div class="col-md-12">
    <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-light">
            <h5 class="mb-0">🎮 My Game & Voice Servers</h5>
            <a href="gameserver-add.php" class="btn btn-sm btn-success">➕ Add New Server</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th></th>
                            <th>🖥️ Hostname</th>
                            <th>🌐 IP Address</th>
                            <th>👥 Players</th>
                            <th>🗺️ Map</th>
                            <th>📡 Status</th>
                            <th><center>⚡ Actions</center></th>
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
                                    <td><center><img src="<?= BASE_URI ?>/GSHQ/uploads/games/<?= htmlspecialchars($servers['games']) ?>.png" width="34" height="34"></center></td>
                                    <td><?= htmlspecialchars($viewer_data['hostname'] ?? 'Pas de réponse') ?></td>
                                    <td><?= htmlspecialchars($servers['adress_ip']) ?>:<?= htmlspecialchars($servers['c_port']) ?></td>
                                    <td><?= $viewer_data['num_players'] ?? 0 ?>/<?= $viewer_data['max_players'] ?? 0 ?></td>
                                    <td><?= htmlspecialchars($viewer_data['map'] ?? '-') ?></td>
                                    <td><?= !empty($viewer_data['online']) ? '✅ Online' : '❌ Offline' ?></td>
                                    <td>
										<center>
											<a href="gameserver-edit.php?id=<?= urlencode($servers['id']) ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
											<a href="gameserver-del.php?id=<?= urlencode($servers['id']) ?>" class="btn btn-sm btn-danger">🗑️ Delete</a>
										</center>
									</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center">No servers available. <a href="gameserver-add.php">Add new Gameserver</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>