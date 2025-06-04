<?php
require_once 'header.php';

// Récupération des utilisateurs depuis la base de données
try {
    $stmt = $conn->query("SELECT * FROM tbl_users ORDER BY user_id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
	<div class="col-md-12">
		<div class="card shadow-sm mt-4">
			<div class="card-header d-flex justify-content-between align-items-center bg-dark text-light">
				<h5 class="mb-0">👥 Users List</h5>
				<a href="users-add.php" class="btn btn-sm btn-success">➕ Add new user</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead class="table-dark">
							<tr>
								<th><center>📝 Pseudo</center></th>
								<th><center>📧 Email</center></th>
								<th><center>⚙️ Status</center></th>
								<th><center>🏅 Rang</center></th>
								<th><center>📅 Registration date</center></th>
								<th><center>⚡ Actions</center></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($users as $user) :
								$date = new DateTime($user['created_at']);
								$formattedDate = $date->format('F j, Y \a\t g:ia');

								// Définition de la classe de couleur selon le rang
								$rang = htmlspecialchars($user['rang']);
								$labelClass = match ($rang) {
									'admin' => 'bg-danger text-white',
									'utilisateur' => 'bg-primary text-white',
									default => 'bg-secondary text-white',
								};
							?>
								<tr>
									<td><center><?= htmlspecialchars($user['pseudo']) ?></center></td>
									<td><center><?= htmlspecialchars($user['email']) ?></center></td>
									<td><center><?= htmlspecialchars($user['status']) ?></center></td>
									<td><center><span class="badge <?= $labelClass ?>"><?= ucfirst($rang) ?></span></center></td>
									<td><center><?= htmlspecialchars($formattedDate) ?></center></td>
									<td>
										<center>
											<a href="users-details.php?id=<?= $user['user_id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
											<?php if ($user['user_id'] != 1): ?>
											<a href="users-del.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger">🗑️ Delete</a>
											<?php endif; ?>
										</center>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php require_once 'footer.php'; ?>
