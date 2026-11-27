<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Peminjam Alat') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['peminjaman_id'])) {
    echo "Peminjaman ID is missing from the URL.";
    exit;
}

$peminjaman_id = $_GET['peminjaman_id'];

$peminjaman_sql = "SELECT p.id, p.total_amount, p.status, p.created_at, p.created_at AS peminjaman_date, dp.return_at
                   FROM peminjaman p
                   JOIN detail_peminjaman dp ON dp.peminjaman_id = p.id
                   WHERE p.id = ?";
$stmt_peminjaman = $conn->prepare($peminjaman_sql);
$stmt_peminjaman->bind_param("i", $peminjaman_id);
$stmt_peminjaman->execute();
$peminjaman_result = $stmt_peminjaman->get_result();

$peminjaman_items_sql = "SELECT dp.*, i.id AS item_id, i.name AS item_name, i.price AS item_price, dp.return_at,
                         dp.quantity,
                         CEIL(DATEDIFF(dp.return_at, p.created_at) / 7) AS total_weeks,
                         (dp.quantity * i.price * CEIL(DATEDIFF(dp.return_at, p.created_at) / 7)) AS total_item_price
                         FROM detail_peminjaman dp
                         JOIN item i ON dp.item_id = i.id
                         JOIN peminjaman p ON dp.peminjaman_id = p.id
                         WHERE dp.peminjaman_id = ?";
$stmt_items = $conn->prepare($peminjaman_items_sql);
$stmt_items->bind_param("i", $peminjaman_id);
$stmt_items->execute();
$peminjaman_items_result = $stmt_items->get_result();

if ($peminjaman_result->num_rows > 0) {
    $peminjaman = $peminjaman_result->fetch_assoc();
    if (strtotime($peminjaman['return_at']) < strtotime($peminjaman['peminjaman_date'])) {
        echo "Error: Return date cannot be earlier than the borrowing date.";
        exit;
    }
} else {
    echo "Peminjaman not found.";
    exit;
}

$status_map = [
    'Pending' => 'status-pending',
    'Dikonfirmasi' => 'status-dikonfirmasi',
    'Menunggu Pembayaran' => 'status-menunggu-pembayaran',
    'Menunggu Konfirmasi Pembayaran' => 'status-menunggu-pembayaran',
    'Pembayaran Dikonfirmasi' => 'status-siap-diambil',
    'Siap Diambil' => 'status-siap-diambil',
    'Sedang Dipinjam' => 'status-sedang-dipinjam',
    'Menunggu Konfirmasi Pengembalian' => 'status-menunggu-pengembalian',
    'Pengembalian Dikonfirmasi' => 'status-pengembalian-dikonfirmasi',
    'Selesai' => 'status-selesai',
];
$badgeClass = $status_map[$peminjaman['status']] ?? 'status-pending';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman #<?php echo (int)$peminjaman['id']; ?> — D'Tools</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?php echo filemtime('assets/css/theme.css'); ?>">
</head>

<body class="app-body has-bottom-nav">
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

            <a href="logout.php" class="header-mobile-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            <button class="nav-toggle" id="hamburger" type="button" aria-label="Toggle Navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="app-nav" id="navbar" aria-label="Navigasi Peminjam">
                <span class="role-pill">Peminjam Alat</span>
                <a href="home.php"><i class="fas fa-house"></i> Home</a>
                <a href="peminjam_dashboard.php"><i class="fas fa-boxes-stacked"></i> Katalog Alat</a>
                <a href="shopping-cart.php"><i class="fas fa-cart-shopping"></i> Keranjang</a>
                <a href="order-history.php" class="active"><i class="fas fa-clock-rotate-left"></i> Riwayat</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="page-header">
            <h1 class="page-title">Peminjaman #<?php echo (int)$peminjaman['id']; ?></h1>
            <div class="page-actions">
                <a href="order-history.php" class="btn-outline-subtle">
                    <i class="fas fa-arrow-left"></i> Riwayat
                </a>
                <a href="home.php" class="btn-brand">
                    <i class="fas fa-house"></i> Dashboard
                </a>
            </div>
        </div>

        <section class="surface-card">
            <div class="surface-card-body">
                <div class="meta-grid">
                    <div class="meta-box">
                        <div class="meta-box-label">ID Peminjaman</div>
                        <div class="meta-box-value cell-mono">#<?php echo (int)$peminjaman['id']; ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-box-label">Status Saat Ini</div>
                        <div class="meta-box-value">
                            <span class="status-badge <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars(ucfirst($peminjaman['status'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-box-label">Tanggal Peminjaman</div>
                        <div class="meta-box-value cell-mono"><?php echo htmlspecialchars($peminjaman['peminjaman_date']); ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-box-label">Tanggal Pengembalian</div>
                        <div class="meta-box-value cell-mono"><?php echo htmlspecialchars($peminjaman['return_at']); ?></div>
                    </div>
                </div>

                <h2 class="surface-card-title" style="margin-bottom: 14px;">
                    <i class="fas fa-boxes-stacked"></i> Rincian Alat
                </h2>

                <div class="dt-table-wrap">
                    <table class="dt-table dt-mobile-list">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Nama Alat</th>
                                <th>Jumlah</th>
                                <th>Harga / Minggu</th>
                                <th>Subtotal / Minggu</th>
                                <th>Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_peminjaman_amount = 0;
                            while ($item = $peminjaman_items_result->fetch_assoc()) {
                                $weeks = max(1, ceil((strtotime($item['return_at']) - strtotime($peminjaman['peminjaman_date'])) / (7 * 24 * 60 * 60)));
                                $weekly_price = $item['quantity'] * $item['item_price'];
                                $total_item_price = $weekly_price * $weeks;
                                $total_peminjaman_amount += $total_item_price;

                                echo "<tr>
                                        <td class='cell-mono col-index' data-label='Item ID'>#{$item['item_id']}</td>
                                        <td class='cell-strong col-primary' data-label='Nama Alat'>" . htmlspecialchars($item['item_name']) . "</td>
                                        <td class='cell-mono' data-label='Jumlah'>{$item['quantity']} unit</td>
                                        <td class='cell-mono' data-label='Harga / Minggu'>Rp " . number_format($item['item_price'], 0, ',', '.') . "</td>
                                        <td class='cell-mono' data-label='Subtotal / Minggu'>Rp " . number_format($weekly_price, 0, ',', '.') . "</td>
                                        <td class='cell-mono' data-label='Total Biaya' style='font-weight:700;'>Rp " . number_format($total_item_price, 0, ',', '.') . "</td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="payment-total-box" style="margin-top: 22px; margin-bottom: 0;">
                    <span>Total Biaya Peminjaman</span>
                    <strong>Rp <?php echo number_format($total_peminjaman_amount, 0, ',', '.'); ?></strong>
                </div>
            </div>
        </section>
    </main>

    <footer class="app-footer">
        <div class="app-footer-inner">
            <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat. All Rights Reserved.</p>
            <p>Portal Peminjam</p>
        </div>
    </footer>

    <nav class="mobile-bottom-nav" aria-label="Navigasi Cepat Peminjam">
        <a href="home.php"><i class="fas fa-house"></i><span>Home</span></a>
        <a href="peminjam_dashboard.php"><i class="fas fa-boxes-stacked"></i><span>Katalog</span></a>
        <a href="shopping-cart.php"><i class="fas fa-cart-shopping"></i><span>Keranjang</span></a>
        <a href="order-history.php" class="active"><i class="fas fa-clock-rotate-left"></i><span>Riwayat</span></a>
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

<?php $conn->close(); ?>
