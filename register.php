<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashed]);
            header("Location: login.php?success=Registered");
            exit;
        } catch (PDOException $e) {
            $error = "Email sudah terdaftar!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Akun - Shorter Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center min-vh-100">
<div class="container" style="max-width: 400px;">
    <div class="card bg-secondary text-white p-4">
        <h3 class="text-center mb-3">Daftar Akun</h3>
        <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Daftar</button>
        </form>
        <p class="mt-3 text-center mb-0">Sudah punya akun? <a href="login.php" class="text-info">Login</a></p>
    </div>
</div>
</body>
</html>