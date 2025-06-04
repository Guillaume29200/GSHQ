<?php
require_once __DIR__ . '/header.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rang = $_POST['rang'];

    if (empty($pseudo) || empty($email) || empty($password) || empty($rang)) {
        $error_message = "❌ Please fill in all fields.";
    } else {
        try {
            // Vérifie si l'email existe déjà
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM tbl_users WHERE email = :email");
            $stmtCheck->execute(['email' => $email]);
            if ($stmtCheck->fetchColumn() > 0) {
                $error_message = "❌ This email is already registered.";
            } else {
                // Insertion
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmtInsert = $conn->prepare("INSERT INTO tbl_users (pseudo, email, password, rang, status, created_at) VALUES (:pseudo, :email, :password, :rang, 'active', NOW())");
                $stmtInsert->execute([
                    'pseudo' => $pseudo,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'rang' => $rang
                ]);
                $success_message = "✅ User successfully added.";
            }
        } catch (PDOException $e) {
            $error_message = "❌ Error: " . $e->getMessage();
        }
    }
}
?>

<div class="col-md-8 offset-md-2 mt-5">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-dark text-light">
            📜 Add New User
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="pseudo" class="form-label">Username</label>
                    <input type="text" class="form-control" id="pseudo" name="pseudo" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="rang" class="form-label">Role</label>
                    <select class="form-select" id="rang" name="rang" required>
                        <option value="admin">Administrator</option>
                        <option value="utilisateur">User (No access admin panel)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">💾 Add User</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
