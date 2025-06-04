<?php
require_once __DIR__ . '/header.php';

// Vérifie si un ID est passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<div class="alert alert-danger">❌ ID utilisateur invalide.</div>');
}

$user_id = intval($_GET['id']);
$success_message = '';
$error_message = '';

// Récupération des messages de succès/erreur après redirection
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = '✅ Information updated successfully.';
}
if (isset($_GET['error']) && $_GET['error'] == 1) {
    $error_message = '❌ An error occurred while updating.';
}

try {
    $stmtUser = $conn->prepare("SELECT pseudo, email, status, rang, created_at FROM tbl_users WHERE user_id = :user_id");
    $stmtUser->execute(['user_id' => $user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('<div class="alert alert-danger">❌ User not found.</div>');
    }
} catch (PDOException $e) {
    die('<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];
    $rang = $_POST['rang'];
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: users-details.php?id=$user_id&error=1");
            exit;
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE tbl_users SET pseudo = :pseudo, email = :email, password = :password, status = :status, rang = :rang WHERE user_id = :user_id";
            $params = [
                'pseudo' => $pseudo,
                'email' => $email,
                'password' => $hashedPassword,
                'status' => $status,
                'rang' => $rang,
                'user_id' => $user_id
            ];
        } else {
            $sql = "UPDATE tbl_users SET pseudo = :pseudo, email = :email, status = :status, rang = :rang WHERE user_id = :user_id";
            $params = [
                'pseudo' => $pseudo,
                'email' => $email,
                'status' => $status,
                'rang' => $rang,
                'user_id' => $user_id
            ];
        }

        $stmtUpdate = $conn->prepare($sql);
        $stmtUpdate->execute($params);

        header("Location: users-details.php?id=$user_id&success=1");
        exit;

    } catch (PDOException $e) {
        header("Location: users-details.php?id=$user_id&error=1");
        exit;
    }
}
?>
<div class="col-md-12">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-light">
            <h5 class="mb-0">👤 User Details : <?= htmlspecialchars($user['pseudo']) ?></h5>
            <a href="users-list.php" class="btn btn-light btn-sm">⬅ Back to user list</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th><center>📧 Email</center></th>
                        <th><center>⚙️ Statut</center></th>
                        <th><center>🏅 Rang</center></th>
                        <th><center>📅 Registration Date</center></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><center><?= htmlspecialchars($user['email']) ?></center></td>
                        <td><center><?= htmlspecialchars($user['status']) ?></center></td>
                        <td><center><?= ucfirst(htmlspecialchars($user['rang'])) ?></center></td>
                        <td><center><?= (new DateTime($user['created_at']))->format('j F Y \a\t H:i') ?></center></td>
                    </tr>
                </tbody>
            </table>
            <hr>
            <h5>✏️ Edit Information</h5>
            <form method="POST" action="">
                <div class="row">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    <div class="col-md-6">
                        <label for="pseudo" class="form-label">Pseudo</label>
                        <input type="text" class="form-control" name="pseudo" id="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email adress</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">New password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password" id="password">
                    </div>
                    <div class="col-md-6">
                        <label for="rang" class="form-label">Rang</label>
                        <select class="form-select" name="rang" id="rang" required>
                            <option value="admin" <?= $user['rang'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                            <option value="utilisateur" <?= $user['rang'] === 'utilisateur' ? 'selected' : '' ?>>User (No access admin panel)</option>
                        </select>
                    </div>					
                    <div class="col-md-12">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                    </div>
                    <br /><br /><br /><br />
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">💾 Save Changes</button>
                    </div>
                </div>
            </form>			
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
