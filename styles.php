<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D'Tools — Sistem Informasi Peminjaman Alat</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?php echo filemtime('assets/css/theme.css'); ?>">
</head>

<body class="app-body">
    <header class="app-header">
        <div class="app-header-inner">
            <a href="home.php" class="brand-lockup">
                <span class="brand-logo-wrap">
                    <img src="logo.png" alt="Logo D'Tools">
                </span>
                <span class="brand-text">
                    <span class="brand-title">D'Tools</span>
                    <span class="brand-subtitle">Sistem Informasi Peminjaman Alat</span>
                </span>
            </a>

            <button class="nav-toggle" id="hamburger" type="button" aria-label="Toggle Navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="app-nav" id="navbar" aria-label="Navigasi Peminjam">
                <span class="role-pill">Peminjam Alat</span>
                <a href="home.php"><i class="fas fa-house"></i> Home</a>
                <a href="peminjam_dashboard.php"><i class="fas fa-boxes-stacked"></i> Katalog Alat</a>
                <a href="shopping-cart.php"><i class="fas fa-cart-shopping"></i> Keranjang</a>
                <a href="order-history.php"><i class="fas fa-clock-rotate-left"></i> Riwayat</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <footer class="app-footer">
        <div class="app-footer-inner">
            <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        const hamburger = document.getElementById('hamburger');
        const navbar = document.getElementById('navbar');
        if (hamburger && navbar) {
            hamburger.addEventListener('click', () => {
                const isOpen = navbar.classList.toggle('active');
                hamburger.setAttribute('aria-expanded', isOpen);
            });
        }
    </script>
</body>

</html>
