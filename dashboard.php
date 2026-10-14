<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = ''; $success = '';

// TAMBAH LINK BARU DENGAN CUSTOM ALIAS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = trim($_POST['original_url']);
    $custom_code = trim($_POST['custom_code']);

    if (!preg_match("~^(?:f|ht)tps?://~i", $original_url)) {
        $original_url = "https://" . $original_url;
    }

    $code = !empty($custom_code) ? $custom_code : substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

    try {
        $stmt = $pdo->prepare("INSERT INTO urls (user_id, original_url, short_code) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $original_url, $code]);
        $success = "Link berhasil dibuat: " . BASE_URL . "/" . $code;
    } catch (PDOException $e) {
        $error = "Custom Alias sudah digunakan! Pilih kata lain.";
    }
}

// AMBIL DAFTAR LINK USER
$stmt = $pdo->prepare("SELECT * FROM urls WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$urls = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard User - Shorter Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<nav class="navbar navbar-dark bg-secondary px-4">
    <a class="navbar-brand fw-bold" href="index.php">shorter.<span class="text-info">site.je</span></a>
    <div>
        <span class="me-3 text-light">Hi, <?= htmlspecialchars($_SESSION['user_email']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container my-5">
    <h2>Dashboard Saya</h2>
    <p class="text-secondary">Link milik user bersifat <strong>PERMANEN</strong> & mendukung Custom Alias.</p>

    <!-- FORM BUAT LINK -->
    <div class="card bg-secondary p-4 mb-4">
        <h4>Buat Link Baru</h4>
        <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <form method="POST" class="row g-2">
            <div class="col-md-6">
                <input type="url" name="original_url" class="form-control" placeholder="URL Asli (https://...)" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="custom_code" class="form-control" placeholder="Custom Alias (Opsional)">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </div>
        </form>
    </div>

    <!-- TABEL KELOLA LINK -->
    <div class="card bg-secondary p-4">
        <h4>Daftar Link Saya</h4>
        <div class="table-responsive">
            <table class="table table-dark table-hover mt-3">
                <thead>
                    <tr>
                        <th>Short Link</th>
                        <th>Original URL</th>
                        <th>Total Klik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($urls)): ?>
                        <tr><td colspan="4" class="text-center">Belum ada link.</td></tr>
                    <?php else: ?>
                        <?php foreach($urls as $row): ?>
                            <tr>
                                <td><a href="<?= BASE_URL . '/' . $row['short_code'] ?>" target="_blank" class="text-info"><?= BASE_URL . '/' . $row['short_code'] ?></a></td>
                                <td class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($row['original_url']) ?></td>
                                <td><span class="badge bg-success"><?= $row['clicks'] ?> Klik</span></td>
                                <td>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus link ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>