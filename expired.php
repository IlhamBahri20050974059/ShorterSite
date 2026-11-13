<?php
require_once 'config.php';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tautan Kedaluwarsa - Shorter Site</title>
    <!-- Bootstrap 5, FontAwesome & Plus Jakarta Sans -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.85);
            --border-color: #334155;
            --accent-blue: #38bdf8;
            --accent-amber: #f59e0b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(56, 189, 248, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(245, 158, 11, 0.08) 0%, transparent 40%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-minimal {
            padding: 1.25rem 0;
        }

        .expired-card {
            background-color: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .expired-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-amber), var(--accent-blue));
        }

        .icon-wrapper {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
            animation: pulseGlow 3s infinite ease-in-out;
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.25);
                transform: translateY(0);
            }
            50% {
                box-shadow: 0 0 25px 6px rgba(245, 158, 11, 0.15);
                transform: translateY(-4px);
            }
        }

        .promo-box {
            background-color: rgba(15, 23, 42, 0.65);
            border: 1px dashed #475569;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            text-align: left;
        }

        .btn-glow {
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border: none;
            color: #0f172a;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .btn-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(56, 189, 248, 0.4);
            color: #0f172a;
        }
    </style>
</head>
<body>

    <!-- Header Minimalis -->
    <header class="navbar-minimal">
        <div class="container text-center">
            <a href="index.php" class="text-decoration-none fw-bold fs-4 text-white">
                <i class="fa-solid fa-link text-info me-2"></i>shorter.<span class="text-info">site.je</span>
            </a>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="container flex-grow-1 d-flex align-items-center justify-content-center py-4">
        <div class="expired-card p-4 p-md-5 text-center">
            
            <!-- Badge Status -->
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1 mb-3 small fw-semibold">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Masa Aktif Habis
            </span>

            <!-- Ikon Animasi -->
            <div class="icon-wrapper">
                <i class="fa-solid fa-hourglass-end fa-2x text-warning"></i>
            </div>

            <!-- Judul & Deskripsi -->
            <h1 class="h3 fw-bold text-white mb-2">Tautan Telah Kedaluwarsa</h1>
            <p class="text-secondary small mb-4 lh-relaxed">
                Maaf, tautan pendek ini dibuat melalui akses tamu dan telah otomatis dihapus oleh sistem karena <strong class="text-light">tidak diakses selama 3 hari berturut-turut</strong>.
            </p>

            <!-- Info Keuntungan Member -->
            <div class="promo-box mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-crown text-info me-2"></i>
                    <span class="fw-bold small text-white">Ingin Tautan Aktif Selamanya?</span>
                </div>
                <p class="text-secondary mb-0" style="font-size: 0.8rem;">
                    Buat akun gratis untuk mendapatkan <span class="text-info fw-semibold">tautan permanen</span> tanpa batas waktu, Custom Alias, QR Code otomatis, serta proteksi password.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="d-grid gap-2">
                <a href="index.php" class="btn btn-glow py-2.5 rounded-3">
                    <i class="fa-solid fa-plus me-1"></i> Buat Tautan Baru
                </a>
                
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="btn btn-outline-light py-2 rounded-3 small fw-semibold">
                        <i class="fa-solid fa-gauge me-1"></i> Kembali ke Dashboard
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-outline-secondary text-light py-2 rounded-3 small fw-semibold">
                        <i class="fa-solid fa-user-plus me-1"></i> Daftar Akun Gratis
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-secondary small">
        <div class="container">
            &copy; <?= date('Y') ?> Shorter Site JE. Cepat, Aman & Bebas Iklan.
        </div>
    </footer>

</body>
</html>
