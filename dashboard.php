<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = ''; $success = '';

// TAMBAH LINK BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = trim($_POST['original_url']);
    $custom_code  = trim($_POST['custom_code']);
    $link_pass    = trim($_POST['link_password']);

    if (!preg_match("~^(?:f|ht)tps?://~i", $original_url)) {
        $original_url = "https://" . $original_url;
    }

    // 🛡️ [LETAK PENGECEKAN] Cek keamanan link sebelum simpan
    if (!isUrlSafeGoogle($original_url)) {
        $error = "Tautan ditolak! URL ini terindikasi berbahaya (Phishing/Malware) oleh Google Safe Browsing.";
    } else {
        $code = !empty($custom_code) ? $custom_code : substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
        $pass_hash = !empty($link_pass) ? password_hash($link_pass, PASSWORD_DEFAULT) : null;

        try {
            $stmt = $pdo->prepare("INSERT INTO urls (user_id, original_url, short_code, password, status) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$user_id, $original_url, $code, $pass_hash]);
            $success = "Tautan berhasil dibuat: " . BASE_URL . "/" . $code;
        } catch (PDOException $e) {
            $error = "Custom Alias sudah digunakan! Silakan pilih kata lain.";
        }
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
    <meta charset="UTF-8">
    <title>Dashboard User - Shorter Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: system-ui, sans-serif; }
        .card-custom { background-color: #1e293b; border: 1px solid #334155; border-radius: 14px; }
        .table-dark { --bs-table-bg: #1e293b; --bs-table-border-color: #334155; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4 border-bottom border-secondary">
    <a class="navbar-brand fw-bold" href="index.php">shorter.<span class="text-info">site.je</span></a>
    <div>
        <span class="text-secondary small me-3"><i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_email']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container my-5">
    <!-- Form Buat Link -->
    <div class="card-custom p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus text-info me-2"></i>Buat Tautan Baru</h5>
        <?php if($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success py-2"><?= $success ?></div><?php endif; ?>
        
        <form method="POST" class="row g-2">
            <div class="col-md-5">
                <input type="url" name="original_url" class="form-control bg-dark text-white border-secondary" placeholder="URL Asli (https://...)" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="custom_code" class="form-control bg-dark text-white border-secondary" placeholder="Custom Alias (Opsional)">
            </div>
            <div class="col-md-2">
                <input type="password" name="link_password" class="form-control bg-dark text-white border-secondary" placeholder="🔒 Password (Opsional)">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="fa-solid fa-link me-1"></i> Simpan</button>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Link -->
    <div class="card-custom p-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list text-info me-2"></i>Daftar Tautan</h5>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tautan Pendek</th>
                        <th>Proteksi</th>
                        <th>Status Link</th>
                        <th>Klik</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($urls)): ?>
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada tautan.</td></tr>
                    <?php else: ?>
                        <?php foreach($urls as $row): 
                            $full_short_url = BASE_URL . '/' . $row['short_code'];
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= $full_short_url ?>" target="_blank" class="text-info fw-semibold text-decoration-none">
                                        <?= $full_short_url ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if(!empty($row['password'])): ?>
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-lock me-1"></i> Password</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary opacity-50">Publik</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Toggle Status Button -->
                                    <a href="toggle_status.php?id=<?= $row['id'] ?>" class="btn btn-sm <?= $row['status'] ? 'btn-success' : 'btn-secondary' ?>">
                                        <i class="fa-solid <?= $row['status'] ? 'fa-toggle-on' : 'fa-toggle-off' ?> me-1"></i>
                                        <?= $row['status'] ? 'Aktif' : 'Nonaktif' ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><?= $row['clicks'] ?> Klik</span></td>
                                <td class="text-end">
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus link ini?')"><i class="fa-solid fa-trash"></i></a>
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
