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

$user_id = $_SESSION['user_id'];

$sql = "SELECT
            p.id AS peminjaman_id,
            p.created_at AS borrow_date,
            p.status AS loan_status,
            p.total_amount,
            d.quantity,
            i.name AS item_name,
            d.price
        FROM peminjaman p
        JOIN detail_peminjaman d ON p.id = d.peminjaman_id
        JOIN item i ON d.item_id = i.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_completed'])) {
    $order_id = $_POST['order_id'];

    $update_query = "UPDATE peminjaman SET status = 'Selesai' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();

    header("Location: order-history.php");
    exit;
}

$orders = [];
$completed_count = 0;
while ($order = $orders_result->fetch_assoc()) {
    $orders[] = $order;
    if ($order['loan_status'] === 'Selesai') {
        $completed_count++;
    }
}

function dt_history_status_class($status) {
    $map = [
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
    return $map[$status] ?? 'status-pending';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman — D'Tools</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">
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
                <a href="order-history.php" class="active"><i class="fas fa-clock-rotate-left"></i> Riwayat</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="page-header">
            <h1 class="page-title">Riwayat Peminjaman</h1>
            <div class="page-actions">
                <a href="peminjam_dashboard.php" class="btn-brand">
                    <i class="fas fa-plus"></i> Pinjam Alat
                </a>
            </div>
        </div>

        <div class="metric-strip" aria-label="Statistik Riwayat">
            <div class="metric-chip"><span class="dot crimson"></span><strong><?php echo count($orders); ?></strong> Total Riwayat</div>
            <div class="metric-chip"><span class="dot emerald"></span><strong><?php echo $completed_count; ?></strong> Selesai</div>
        </div>

        <section class="surface-card">
            <div class="surface-card-body">
                <?php if (count($orders) > 0): ?>
                    <div class="dt-table-wrap">
                        <table id="orderTable" class="dt-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Alat</th>
                                    <th>Tanggal</th>
                                    <th>Total Biaya</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php $badgeClass = dt_history_status_class($order['loan_status']); ?>
                                    <tr>
                                        <td class="cell-mono">#<?php echo (int)$order['peminjaman_id']; ?></td>
                                        <td class="cell-strong"><?php echo htmlspecialchars($order['item_name']); ?></td>
                                        <td class="cell-mono"><?php echo htmlspecialchars($order['borrow_date']); ?></td>
                                        <td class="cell-mono">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($order['loan_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($order['loan_status'] == 'Siap Diambil' || $order['loan_status'] == 'Sedang Dipinjam'): ?>
                                                <form method="POST" class="cell-actions">
                                                    <input type="hidden" name="order_id" value="<?php echo (int)$order['peminjaman_id']; ?>">
                                                    <button type="submit" name="mark_completed" class="btn-soft-success btn-sm-action">
                                                        <i class="fas fa-check"></i> Selesai
                                                    </button>
                                                    <a href="order-details.php?peminjaman_id=<?php echo (int)$order['peminjaman_id']; ?>" class="btn-outline-subtle btn-sm-action">
                                                        Detail
                                                    </a>
                                                </form>
                                            <?php else: ?>
                                                <a href="order-details.php?peminjaman_id=<?php echo (int)$order['peminjaman_id']; ?>" class="btn-outline-subtle btn-sm-action">
                                                    <i class="fas fa-eye"></i> Lihat Detail
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-clock-rotate-left"></i></div>
                        <h3>Belum Ada Riwayat Peminjaman</h3>
                        <p>Transaksi peminjaman alat yang Anda ajukan akan tampil di sini.</p>
                        <a href="peminjam_dashboard.php" class="btn-brand">Mulai Pinjam Alat</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="app-footer">
        <div class="app-footer-inner">
            <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat.</p>
            <p>Portal Peminjam</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            if ($('#orderTable').length) {
                $('#orderTable').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    lengthChange: true,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ entri",
                        info: "Menampilkan _START_–_END_ dari _TOTAL_ riwayat",
                        paginate: {
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });
            }
        });

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
