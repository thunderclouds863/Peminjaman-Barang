<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";
$conn = demo_mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Peminjaman Confirmation
if (isset($_GET['confirm_peminjaman'])) {
    $peminjaman_id = $_GET['confirm_peminjaman'];

    $update_status = "UPDATE peminjaman SET status = 'Dikonfirmasi' WHERE id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $peminjaman_id);
    $stmt->execute();

    $peminjaman_items = "SELECT item_id, quantity FROM detail_peminjaman WHERE peminjaman_id = ?";
    $stmt = $conn->prepare($peminjaman_items);
    $stmt->bind_param("i", $peminjaman_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $item_id = $item['item_id'];
        $quantity = $item['quantity'];

        $update_stock = "UPDATE item SET stock = stock - ? WHERE id = ?";
        $stmt = $conn->prepare($update_stock);
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
    }

    header("Location: order-management.php");
    exit();
}

if (isset($_GET['confirm_pengembalian'])) {
    $peminjaman_id = $_GET['confirm_pengembalian'];

    $peminjaman_items = "SELECT item_id, quantity FROM detail_peminjaman WHERE peminjaman_id = ?";
    $stmt = $conn->prepare($peminjaman_items);
    $stmt->bind_param("i", $peminjaman_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $update_status = "UPDATE peminjaman SET status = 'Pengembalian Dikonfirmasi' WHERE id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $peminjaman_id);
    $stmt->execute();

    while ($item = $result->fetch_assoc()) {
        $item_id = $item['item_id'];
        $quantity = $item['quantity'];

        $update_stock = "UPDATE item SET stock = stock + ? WHERE id = ?";
        $stmt = $conn->prepare($update_stock);
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
    }

    header("Location: order-management.php");
    exit();
}

$sql = "SELECT peminjaman.id, peminjaman.created_at, peminjaman.total_amount, peminjaman.status, users.username
        FROM peminjaman
        JOIN users ON peminjaman.user_id = users.id
        ORDER BY peminjaman.created_at DESC";
$result = $conn->query($sql);

$orders = [];
$pending_count = 0;
$return_wait_count = 0;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
        if ($row['status'] === 'Pending') {
            $pending_count++;
        }
        if ($row['status'] === 'Menunggu Konfirmasi Pengembalian') {
            $return_wait_count++;
        }
    }
}

function dt_order_status_class($status) {
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
    <title>Kelola Peminjaman — D'Tools Divisi Alat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?php echo filemtime('assets/css/theme.css'); ?>">
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
                    <span class="brand-subtitle">Operasional Divisi Alat</span>
                </span>
            </a>

            <a href="logout.php" class="header-mobile-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            <button class="nav-toggle" id="hamburger" type="button" aria-label="Toggle Navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="app-nav" id="navbar" aria-label="Navigasi Divisi Alat">
                <span class="role-pill">Divisi Alat</span>
                <a href="divisi_dashboard.php"><i class="fas fa-house"></i> Home</a>
                <a href="inventory_management.php"><i class="fas fa-boxes-stacked"></i> Inventory</a>
                <a href="order-management.php" class="active"><i class="fas fa-clipboard-check"></i> Peminjaman</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="page-header">
            <h1 class="page-title">Daftar Peminjaman</h1>
            <div class="page-actions">
                <a href="inventory_management.php" class="btn-outline-subtle">
                    <i class="fas fa-boxes-stacked"></i> Inventory
                </a>
            </div>
        </div>

        <div class="metric-strip" aria-label="Ringkasan Peminjaman">
            <div class="metric-chip"><span class="dot crimson"></span><strong><?php echo count($orders); ?></strong> Total</div>
            <div class="metric-chip"><span class="dot amber"></span><strong><?php echo $pending_count; ?></strong> Pending</div>
            <div class="metric-chip"><span class="dot blue"></span><strong><?php echo $return_wait_count; ?></strong> Pengembalian</div>
        </div>

        <section class="surface-card">
            <div class="surface-card-body">
                <div class="dt-table-wrap">
                    <table id="orderTable" class="dt-table dt-mobile-list">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Peminjam</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $row): ?>
                                <?php $badgeClass = dt_order_status_class($row['status']); ?>
                                <tr>
                                    <td class="cell-mono col-primary" data-label="ID">Peminjaman #<?php echo (int)$row['id']; ?></td>
                                    <td class="cell-strong" data-label="Peminjam"><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td class="cell-mono" data-label="Tanggal"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td class="cell-mono" data-label="Total Biaya">Rp <?php echo number_format((float)$row['total_amount'], 0, ',', '.'); ?></td>
                                    <td data-label="Status">
                                        <span class="status-badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="col-actions" data-label="Aksi">
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <a href="order-management.php?confirm_peminjaman=<?php echo (int)$row['id']; ?>" class="btn-brand btn-sm-action">
                                                <i class="fas fa-check"></i> Konfirmasi Peminjaman
                                            </a>
                                        <?php elseif ($row['status'] == 'Menunggu Konfirmasi Pengembalian'): ?>
                                            <a href="order-management.php?confirm_pengembalian=<?php echo (int)$row['id']; ?>" class="btn-soft-success btn-sm-action">
                                                <i class="fas fa-rotate-left"></i> Konfirmasi Pengembalian
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--ink-400); font-size: 12.5px;">Tidak ada aksi</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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

    <nav class="mobile-bottom-nav" aria-label="Navigasi Cepat Divisi Alat">
        <a href="divisi_dashboard.php"><i class="fas fa-house"></i><span>Home</span></a>
        <a href="inventory_management.php"><i class="fas fa-boxes-stacked"></i><span>Inventory</span></a>
        <a href="order-management.php" class="active"><i class="fas fa-clipboard-check"></i><span>Peminjaman</span></a>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#orderTable').DataTable({
                order: [[0, 'desc']],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ peminjaman",
                    emptyTable: "Belum ada data peminjaman.",
                    paginate: {
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
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
