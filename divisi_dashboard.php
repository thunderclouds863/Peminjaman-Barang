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

<body class="app-body">
  <header class="app-header">
    <div class="app-header-inner">
      <a href="divisi_dashboard.php" class="brand-lockup">
        <span class="brand-logo-wrap">
          <img src="logo.png" alt="Logo D'Tools">
        </span>
        <span class="brand-text">
          <span class="brand-title">D'Tools</span>
          <span class="brand-subtitle">Operasional Divisi Alat</span>
        </span>
      </a>

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
    <section class="hub-hero">
      <div class="hub-hero-inner">
        <div>
          <div class="page-eyebrow" style="color: #fda4af;"><i class="fas fa-screwdriver-wrench"></i> Panel Operasional</div>
          <h1>Selamat Datang, Divisi Alat!</h1>
          <p>
            Kelola katalog inventaris laboratorium, pantau ketersediaan stok unit, serta konfirmasi permintaan peminjaman dan pengembalian alat dengan cepat.
          </p>
        </div>
        <div class="page-actions">
          <a href="inventory_management.php" class="btn-brand">
            <i class="fas fa-boxes-stacked"></i> Kelola Inventory
          </a>
          <a href="order-management.php" class="btn-outline-subtle" style="background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.22);">
            <i class="fas fa-clipboard-check"></i> Cek Peminjaman
          </a>
        </div>
      </div>
    </section>

    <section class="hub-grid" aria-label="Menu Utama Divisi Alat">
      <a href="inventory_management.php" class="hub-card">
        <div>
          <div class="hub-card-icon"><i class="fas fa-boxes-stacked"></i></div>
          <h3 style="margin-top: 16px;">Manajemen Inventaris</h3>
          <p>Tambahkan peralatan baru, perbarui deskripsi, tarif sewa mingguan, dan jumlah stok yang tersedia di gudang.</p>
        </div>
        <span class="hub-card-link">Buka Inventaris <i class="fas fa-arrow-right"></i></span>
      </a>

      <a href="order-management.php" class="hub-card">
        <div>
          <div class="hub-card-icon"><i class="fas fa-clipboard-check"></i></div>
          <h3 style="margin-top: 16px;">Kelola Peminjaman & Pengembalian</h3>
          <p>Verifikasi pengajuan peminjaman baru (stok otomatis berkurang) dan konfirmasi alat yang dikembalikan peminjam.</p>
        </div>
        <span class="hub-card-link">Lihat Daftar Peminjaman <i class="fas fa-arrow-right"></i></span>
      </a>
    </section>

    <section aria-label="Alur Kerja Divisi Alat">
      <h2 class="surface-card-title" style="margin-bottom: 14px;">
        <i class="fas fa-route"></i> Alur Operasional Standar
      </h2>
      <div class="workflow-strip">
        <div class="workflow-step">
          <span class="workflow-step-num">01 / STOK</span>
          <h4>Pastikan Katalog Akurat</h4>
          <p>Perbarui stok dan kondisi alat di halaman Inventory sebelum periode peminjaman dimulai.</p>
        </div>
        <div class="workflow-step">
          <span class="workflow-step-num">02 / KONFIRMASI</span>
          <h4>Setujui Pengajuan Pending</h4>
          <p>Konfirmasi pesanan baru agar peminjam dapat melanjutkan ke proses pembayaran ke Bendahara.</p>
        </div>
        <div class="workflow-step">
          <span class="workflow-step-num">03 / PENGEMBALIAN</span>
          <h4>Validasi Pengembalian</h4>
          <p>Periksa kondisi fisik alat yang kembali lalu klik Konfirmasi Pengembalian untuk memulihkan stok.</p>
        </div>
      </div>
    </section>
  </main>

  <footer class="app-footer">
    <div class="app-footer-inner">
      <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat. All Rights Reserved.</p>
      <p>Portal Divisi Alat</p>
    </div>
  </footer>

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
