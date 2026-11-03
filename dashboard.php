<?php
require_once 'config.php';

// Wajib Login
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Shorter Site</title>
    <!-- Bootstrap 5 & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }
        .navbar {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
        }
        .card-custom {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
        }
        .table-dark {
            --bs-table-bg: #1e293b;
            --bs-table-border-color: #334155;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4">
    <a class="navbar-brand fw-bold" href="index.php">
        <i class="fa-solid fa-link text-info me-2"></i>shorter.<span class="text-info">site.je</span>
    </a>
    <div class="d-flex align-items-center gap-3">
        <span class="text-secondary small d-none d-md-inline"><i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_email']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
    </div>
</nav>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Link Saya</h2>
            <p class="text-secondary small mb-0">Tautan kamu bersifat <strong>permanen</strong> & mendukung statistik serta QR Code.</p>
        </div>
    </div>

    <!-- Form Buat Link -->
    <div class="card-custom p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus text-info me-2"></i>Buat Tautan Baru</h5>
        <?php if($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success py-2"><?= $success ?></div><?php endif; ?>
        
        <form method="POST" class="row g-2">
            <div class="col-md-6">
                <input type="url" name="original_url" class="form-control bg-dark text-white border-secondary" placeholder="URL Asli (https://...)" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="custom_code" class="form-control bg-dark text-white border-secondary" placeholder="Custom Alias (Opsional)">
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
                        <th>URL Tujuan</th>
                        <th>Total Klik</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($urls)): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada tautan yang dibuat.</td></tr>
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
                                <td class="text-truncate text-secondary" style="max-width: 250px;">
                                    <?= htmlspecialchars($row['original_url']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                        <i class="fa-solid fa-chart-simple me-1"></i> <?= $row['clicks'] ?> Klik
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-info btn-sm me-1" 
                                            onclick="showQrModal('<?= $full_short_url ?>', '<?= $row['short_code'] ?>')">
                                        <i class="fa-solid fa-qrcode me-1"></i> QR Code
                                    </button>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin menghapus tautan ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark text-white border-secondary text-center">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold fs-6"><i class="fa-solid fa-qrcode text-info me-2"></i>QR Code Tautan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="p-3 bg-white d-inline-block rounded-3 shadow-sm mb-3">
                    <img id="qrImage" src="" alt="QR Code" class="img-fluid" style="width: 200px; height: 200px;">
                </div>
                <p id="qrUrlText" class="small text-info fw-semibold text-break mb-0"></p>
            </div>
            <div class="modal-footer border-secondary justify-content-center">
                <button type="button" id="downloadQrBtn" class="btn btn-primary btn-sm fw-semibold w-100">
                    <i class="fa-solid fa-download me-1"></i> Unduh Gambar (PNG)
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentQrUrl = '';
let currentCode = '';

function showQrModal(fullUrl, code) {
    currentQrUrl = fullUrl;
    currentCode = code;

    // API QR Code gratis dari QRServer
    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(fullUrl)}`;
    
    document.getElementById('qrImage').src = qrApiUrl;
    document.getElementById('qrUrlText').innerText = fullUrl;

    const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
    qrModal.show();
}

// Fungsi Unduh Gambar PNG
document.getElementById('downloadQrBtn').addEventListener('click', async function() {
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Mengunduh...';

    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${encodeURIComponent(currentQrUrl)}`;

    try {
        const response = await fetch(qrApiUrl);
        const blob = await response.blob();
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `QR-${currentCode}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        alert("Gagal mengunduh gambar QR Code.");
    } finally {
        btn.innerHTML = originalText;
    }
});
</script>
</body>
</html>
