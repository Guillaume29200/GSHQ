<?php
require_once __DIR__ . '/header-login.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pseudo = trim($_POST['pseudo']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT user_id, password, rang, status FROM tbl_users WHERE pseudo = :pseudo");
    $stmt->execute([':pseudo' => $pseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active') {
            $error = "Your account is inactive.";
        } else {
            // Régénération de l'ID de session pour éviter les attaques de fixation de session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_rang'] = $user['rang'];
            
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Incorrect login credentials.";
    }
}
?>
	<div class="col-md-12">
		<div class="card shadow-sm mt-4">
			<div class="card-header d-flex justify-content-between align-items-center bg-dark text-light">
				<h5 class="mb-0">🔑 <?= htmlspecialchars(WEBSITE_NAME) ?> - Administration Login</h5>
			</div>
			<div class="card-body">
				<p class="text-muted">Administrator-only area.</p>
				<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
				<form action="" method="POST">
					<div class="form-floating mb-3">
						<input type="text" name="pseudo" class="form-control" id="floatingInput" required>
						<label for="floatingInput">Pseudo</label>
					</div>
					<div class="form-floating mb-3">
						<input type="password" name="password" class="form-control" id="floatingPassword" required>
						<label for="floatingPassword">Password</label>
					</div>
					<button type="submit" class="btn btn-primary w-100 py-2">Log in</button>
				</form>
			</div>
		</div>
	</div>
<?php require_once __DIR__ . '/footer.php'; ?>
