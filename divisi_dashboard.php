<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Divisi Alat — D'Tools</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css">
</head>

<body class="app-body has-bottom-nav">
  <header class="app-header">
    <div class="app-header-inner">
      <a href="divisi_dashboard.php" class="brand-lockup">
        <span class="brand-logo-wrap">
          <img src="logo.png" alt="Logo D'Tools">
        </span>
        <span class="brand-text">
          <span class="brand-title">D'Tools</span>
          <span class="brand-subtitle">Divisi Alat</span>
        </span>
      </a>

      <a href="logout.php" class="header-mobile-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
      <button class="nav-toggle" id="hamburger" type="button" aria-label="Toggle Navigation" aria-expanded="false">
        <i class="fas fa-bars"></i>
      </button>

      <nav class="app-nav" id="navbar" aria-label="Navigasi Divisi Alat">
        <span class="role-pill">Divisi Alat</span>
        <a href="divisi_dashboard.php" class="active"><i class="fas fa-house"></i> Home</a>
        <a href="inventory_management.php"><i class="fas fa-boxes-stacked"></i> Inventory</a>
        <a href="order-management.php"><i class="fas fa-clipboard-check"></i> Peminjaman</a>
        <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
      </nav>
    </div>
  </header>

  <main class="app-main">
    <div class="ops-grid">
      <section class="hub-hero" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <div class="hub-hero-inner" style="display: block;">
          <h1>Selamat Datang, Divisi Alat!</h1>
          <p style="margin-top: 10px;">
            Kelola inventaris, pantau peminjaman, dan pastikan ketersediaan alat untuk peminjam.
          </p>
        </div>

        <div class="auth-visual-flow" style="margin-top: 28px;" aria-hidden="true">
          <div class="flow-track">
            <div class="flow-node">
              <i class="fas fa-cubes"></i>
              <span>Stok Alat</span>
            </div>
            <div class="flow-node">
              <i class="fas fa-check-double"></i>
              <span>Konfirmasi</span>
            </div>
            <div class="flow-node">
              <i class="fas fa-box-open"></i>
              <span>Dipinjam</span>
            </div>
            <div class="flow-node">
              <i class="fas fa-rotate-left"></i>
              <span>Kembali</span>
            </div>
          </div>
        </div>
      </section>

      <section class="ops-stack" aria-label="Menu Divisi Alat">
        <a href="inventory_management.php" class="ops-tile">
          <div class="ops-tile-left">
            <div class="hub-card-icon"><i class="fas fa-boxes-stacked"></i></div>
            <div>
              <h3>Inventory</h3>
              <p>Tambah, edit, dan atur stok alat.</p>
            </div>
          </div>
          <span class="ops-tile-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="order-management.php" class="ops-tile">
          <div class="ops-tile-left">
            <div class="hub-card-icon"><i class="fas fa-clipboard-check"></i></div>
            <div>
              <h3>Peminjaman</h3>
              <p>Konfirmasi pengajuan dan pengembalian.</p>
            </div>
          </div>
          <span class="ops-tile-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
      </section>
    </div>
  </main>

  <footer class="app-footer">
    <div class="app-footer-inner">
      <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat.</p>
      <p>Divisi Alat</p>
    </div>
  </footer>

  <nav class="mobile-bottom-nav" aria-label="Navigasi Cepat Divisi Alat">
    <a href="divisi_dashboard.php" class="active"><i class="fas fa-house"></i><span>Home</span></a>
    <a href="inventory_management.php"><i class="fas fa-boxes-stacked"></i><span>Inventory</span></a>
    <a href="order-management.php"><i class="fas fa-clipboard-check"></i><span>Peminjaman</span></a>
  </nav>

  <script>
    const hamburger = document.getElementById('hamburger');
    const navbar = document.getElementById('navbar');
    hamburger.addEventListener('click', () => {
      const isOpen = navbar.classList.toggle('active');
      hamburger.setAttribute('aria-expanded', isOpen);
    });
  </script>
</body>

</html>
