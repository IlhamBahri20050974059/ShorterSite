<?php
require_once 'config.php';

// --- BILA ADA PARAMETER CODE (PEMBACAAN SHORTLINK) ---
if (isset($_GET['code'])) {
    $code = trim($_GET['code']);

    $stmt = $pdo->prepare("SELECT * FROM urls WHERE short_code = ?");
    $stmt->execute([$code]);
    $url = $stmt->fetch();

    if ($url) {
        // CEK LAZY CLEANUP: Jika Guest (user_id NULL)
        if (is_null($url['user_id'])) {
            $lastAccess = strtotime($url['last_accessed_at'] ?? $url['created_at']);
            $diffDays = (time() - $lastAccess) / (60 * 60 * 24);

            // Jika > 3 Hari tidak diakses -> Hapus & Arahkan ke Expired
            if ($diffDays >= 3) {
                $delStmt = $pdo->prepare("DELETE FROM urls WHERE id = ?");
                $delStmt->execute([$url['id']]);

                header("Location: expired.php");
                exit;
            }
        }

        // BILA MASIH VALID: Update Klik & Tanggal Akses Terakhir
        $updStmt = $pdo->prepare("UPDATE urls SET clicks = clicks + 1, last_accessed_at = NOW() WHERE id = ?");
        $updStmt->execute([$url['id']]);

        // Redirect ke URL Asli
        header("Location: " . $url['original_url']);
        exit;
    } else {
        echo "<h3>404 - Link tidak ditemukan!</h3>";
        exit;
    }
}

// --- PEMBUATAN LINK GUEST ---
$short_result = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = trim($_POST['original_url']);

    if (!preg_match("~^(?:f|ht)tps?://~i", $original_url)) {
        $original_url = "https://" . $original_url;
    }

    // Buat Kode Acak 6 Karakter
    $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

    try {
        $stmt = $pdo->prepare("INSERT INTO urls (user_id, original_url, short_code) VALUES (NULL, ?, ?)");
        $stmt->execute([$original_url, $code]);
        $short_result = BASE_URL . "/" . $code;
    } catch (PDOException $e) {
        $error = "Gagal membuat link. Coba lagi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="r-npCCgT6pZhlQr2mDkWcTiFxiVEJs-Hk1HSH-6A7Ac" />
    <title>Shorter Site JE - Premium URL Shortener</title>
    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --accent-blue: #38bdf8;
            --accent-indigo: #6366f1;
            --border-color: #334155;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: #f8fafc;
        }

        .navbar {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
        }

        .hero-section {
            padding: 5rem 0 4rem;
            background: radial-gradient(circle at top, rgba(99, 102, 241, 0.15) 0%, transparent 60%);
        }

        .hero-title {
            font-weight: 800;
            font-size: 2.8rem;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .shortener-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
        }

        .feature-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1.8rem;
            transition: transform 0.2s, border-color 0.2s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-indigo);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .step-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent-indigo);
            color: #fff;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .cta-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #0284c7 100%);
            border-radius: 20px;
            padding: 3rem 2rem;
        }

        footer {
            border-top: 1px solid var(--border-color);
            background-color: #0b0f19;
        }
    </style>
    
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            <i class="fa-solid fa-link text-info me-2"></i>shorter.<span class="text-info">site.je</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2 mt-3 mt-lg-0">
                <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
                <li class="nav-item"><a class="nav-link" href="#cara-kerja">Cara Kerja</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a href="dashboard.php" class="btn btn-info btn-sm px-3 fw-semibold">Dashboard</a></li>
                    <li class="nav-item"><a href="logout.php" class="btn btn-outline-light btn-sm px-3">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a href="login.php" class="btn btn-outline-light btn-sm px-3">Login</a></li>
                    <li class="nav-item"><a href="register.php" class="btn btn-primary btn-sm px-3 fw-semibold">Daftar Gratis</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section & Shortener Form -->
<section class="hero-section">
    <div class="container text-center">
        <span class="badge bg-indigo bg-opacity-20 text-info px-3 py-2 rounded-pill mb-3 border border-info border-opacity-25">
            <i class="fa-solid fa-bolt me-1"></i> Platform URL Shortener Cepat & Modern
        </span>
        <h1 class="hero-title mb-3">Persingkat Tautan Panjangmu<br>Dalam Hitungan Detik</h1>
        <p class="text-secondary fs-5 mx-auto mb-4" style="max-width: 650px;">
            Shorter Site JE membantu mempermudah pembagian link, melacak statistik klik, dan mempercantik tautan bisnis atau portofoliomu.
        </p>

        <!-- Form Box -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="shortener-card p-4 text-start mb-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php">
                        <label class="form-label text-secondary small fw-semibold">Tempelkan URL Asli Kamu</label>
                        <div class="input-group input-group-lg mb-2">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-globe"></i></span>
                            <input type="url" name="original_url" class="form-control bg-dark text-white border-secondary fs-6" placeholder="https://contoh-link-panjang-sekali.com/halaman" required>
                            <button class="btn btn-primary fw-bold px-4" type="submit">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Persingkat
                            </button>
                        </div>
                        <span class="text-muted style-small" style="font-size: 0.8rem;">
                            <i class="fa-solid fa-circle-info text-info me-1"></i> Mode Tamu: Link aktif tanpa login (Nonaktif jika tidak diakses >3 hari).
                        </span>
                    </form>

                    <!-- Result Alert with Copy Button -->
                    <?php if($short_result): ?>
                        <div class="alert alert-success bg-success bg-opacity-10 border-success text-white mt-4 p-3 rounded-3">
                            <label class="form-label text-success fw-bold mb-2">
                                <i class="fa-solid fa-circle-check me-1"></i> Tautan Pendek Berhasil Dibuat:
                            </label>
                            <div class="input-group">
                                <input type="text" id="shortUrlInput" class="form-control bg-dark text-info border-secondary fw-bold" value="<?= $short_result ?>" readonly>
                                <button class="btn btn-success fw-bold px-4" type="button" onclick="copyLink(this)">
                                    <i class="fa-regular fa-copy me-1"></i> Salin
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Penjelasan Fitur -->
<section id="fitur" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Kenapa Memilih Shorter Site JE?</h2>
            <p class="text-secondary">Solusi serba ada untuk kebutuhan pengelolaan tautanmu</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon"><i class="fa-solid fa-bolt-lightning"></i></div>
                    <h5 class="fw-bold">Serba Cepat & Tanpa Ribet</h5>
                    <p class="text-secondary small mb-0">Persingkat tautan secara instan langsung dari halaman depan tanpa wajib login terlebih dahulu.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                    <h5 class="fw-bold">Custom Alias Tautan</h5>
                    <p class="text-secondary small mb-0">Pengguna terdaftar dapat memilih kata kunci unik sesuai brand (contoh: <code>shorter.site.je/promo-2026</code>).</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <h5 class="fw-bold">Statistik & Pantauan Klik</h5>
                    <p class="text-secondary small mb-0">Pantau jumlah total pengunjung dan performa setiap link kamu melalui dashboard interaktif.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Cara Kerja -->
<section id="cara-kerja" class="py-5 bg-opacity-50" style="background-color: #0b0f19;">
    <div class="container text-center">
        <h2 class="fw-bold mb-5">3 Langkah Mudah</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-number">1</div>
                <h5 class="fw-bold">Tempelkan Link</h5>
                <p class="text-secondary small">Masukkan URL panjang yang ingin kamu persingkat pada kolom yang telah disediakan.</p>
            </div>
            <div class="col-md-4">
                <div class="step-number">2</div>
                <h5 class="fw-bold">Klik Persingkat</h5>
                <p class="text-secondary small">Sistem kami akan memproses dan menggenerasi tautan pendek unik secara otomatis.</p>
            </div>
            <div class="col-md-4">
                <div class="step-number">3</div>
                <h5 class="fw-bold">Bagikan & Pantau</h5>
                <p class="text-secondary small">Salin hasil tautan pendek dan bagikan ke sosial media, kawan, atau klien bisnis kamu.</p>
            </div>
        </div>
    </div>
</section>

<!-- Banner CTA Register -->
<section class="py-5">
    <div class="container">
        <div class="cta-banner text-center text-white">
            <h2 class="fw-extrabold mb-3">Dapatkan Tautan Permanen Tanpa Batas</h2>
            <p class="fs-6 opacity-90 mx-auto mb-4" style="max-width: 600px;">
                Daftar akun gratis sekarang untuk menikmati link permanen (tanpa terhapus otomatis), custom alias pilihanmu, serta manajemen dashboard lengkap.
            </p>
            <a href="register.php" class="btn btn-dark btn-lg px-5 fw-bold rounded-pill">Daftar Akun Gratis</a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="py-4 mt-5">
    <div class="container text-center text-secondary small">
        <div class="mb-2">
            <a href="index.php" class="text-light text-decoration-none me-3">Beranda</a>
            <a href="login.php" class="text-light text-decoration-none me-3">Login</a>
            <a href="register.php" class="text-light text-decoration-none">Daftar</a>
        </div>
        <p class="mb-0">&copy; 2026 Ilham Bahri Albi. All Rights Reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyLink(btn) {
    const copyText = document.getElementById("shortUrlInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(copyText.value).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Tersalin!';
        btn.classList.replace("btn-success", "btn-light");

        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.replace("btn-light", "btn-success");
        }, 2000);
    });
}
</script>
</body>
</html>