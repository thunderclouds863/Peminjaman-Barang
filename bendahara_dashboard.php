<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Bendahara') {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proses perubahan status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (isset($_POST['peminjaman_id']) && isset($_POST['new_status'])) {
        $peminjaman_id = $_POST['peminjaman_id'];
        $new_status = $_POST['new_status'];

        $update_query = "UPDATE peminjaman SET status='$new_status' WHERE id=$peminjaman_id";
        if (mysqli_query($conn, $update_query)) {
            header("Location: bendahara_dashboard.php");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }
}

// Mengambil peminjaman dengan status tertentu
$query = "SELECT * FROM peminjaman WHERE status IN ('Menunggu Konfirmasi Pembayaran')";
$result = mysqli_query($conn, $query);

// Menangani upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_payment_proof'])) {
    if (isset($_POST['peminjaman_id']) && isset($_FILES['payment_proof'])) {
        $peminjaman_id = $_POST['peminjaman_id'];
        $payment_proof = $_FILES['payment_proof'];

        $upload_dir = 'uploads/';
        $file_tmp = $payment_proof['tmp_name'];
        $file_extension = pathinfo($payment_proof['name'], PATHINFO_EXTENSION);

        $new_file_name = 'bukti' . $peminjaman_id . '.' . $file_extension;
        $upload_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $upload_path)) {
            $update_payment_query = "UPDATE peminjaman SET payment_proof='$new_file_name' WHERE id=$peminjaman_id";
            if (mysqli_query($conn, $update_payment_query)) {
                header("Location: bendahara_dashboard.php");
                exit();
            } else {
                echo "Error updating payment proof: " . mysqli_error($conn);
            }
        } else {
            echo "Failed to upload payment proof.";
        }
    }
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$pending_payments = [];
$total_pending_amount = 0;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pending_payments[] = $row;
        $total_pending_amount += (float)($row['total_amount'] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bendahara — D'Tools</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?php echo filemtime('assets/css/theme.css'); ?>">
</head>

<body class="app-body has-bottom-nav">
    <header class="app-header">
        <div class="app-header-inner">
            <a href="bendahara_dashboard.php" class="brand-lockup">
                <span class="brand-logo-wrap">
                    <img src="logo.png" alt="Logo D'Tools">
                </span>
                <span class="brand-text">
                    <span class="brand-title">D'Tools</span>
                    <span class="brand-subtitle">Portal Verifikasi Bendahara</span>
                </span>
            </a>

            <a href="?logout=true" class="header-mobile-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            <button class="nav-toggle" id="hamburger" type="button" aria-label="Toggle Navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="app-nav" id="navbar" aria-label="Navigasi Bendahara">
                <span class="role-pill">Bendahara</span>
                <a href="bendahara_dashboard.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Verifikasi Pembayaran</a>
                <a href="?logout=true" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="page-header">
            <h1 class="page-title">Verifikasi Pembayaran</h1>
            <div class="metric-chips" aria-label="Ringkasan Pembayaran">
                <div class="metric-chip"><span class="dot amber"></span><strong><?php echo count($pending_payments); ?></strong> Antrean</div>
                <div class="metric-chip accent"><strong>Rp <?php echo number_format($total_pending_amount, 0, ',', '.'); ?></strong></div>
            </div>
        </div>

        <section class="surface-card">
            <div class="surface-card-body">
                <?php if (empty($pending_payments)): ?>
                    <div class="empty-state">
                        <img src="assets/svg/empty-history.svg" alt="" class="empty-state-svg" aria-hidden="true">
                        <h3>Semua Pembayaran Terverifikasi</h3>
                        <p>Belum ada antrean bukti pembayaran yang menunggu konfirmasi saat ini.</p>
                    </div>
                <?php else: ?>
                <div class="dt-table-wrap">
                    <table id="orderTable" class="dt-table dt-mobile-list">
                        <thead>
                            <tr>
                                <th>ID Peminjaman</th>
                                <th>Total Nominal</th>
                                <th>Status</th>
                                <th>Bukti Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_payments as $row): ?>
                                <tr>
                                    <td class="cell-mono col-primary" data-label="ID Peminjaman">Peminjaman #<?php echo (int)$row['id']; ?></td>
                                    <td class="cell-mono" data-label="Total Nominal">Rp <?php echo number_format((float)($row['total_amount'] ?? 0), 0, ',', '.'); ?></td>
                                    <td data-label="Status">
                                        <span class="status-badge status-menunggu-pembayaran">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Bukti">
                                        <?php
                                        $upload_dir = 'uploads/';
                                        $peminjaman_id = $row['id'];
                                        $allowed_extensions = ['png', 'jpg', 'jpeg'];
                                        $file_found = false;
                                        $file_path = '';
                                        foreach ($allowed_extensions as $ext) {
                                            $file_name = 'bukti' . $peminjaman_id . '.' . $ext;
                                            $candidate = $upload_dir . $file_name;
                                            if (file_exists($candidate)) {
                                                $file_found = true;
                                                $file_path = $candidate;
                                                break;
                                            }
                                        }

                                        if ($file_found):
                                        ?>
                                            <a href="<?php echo htmlspecialchars($file_path, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn-outline-subtle btn-sm-action">
                                                <i class="fas fa-image"></i> Lihat Bukti
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--ink-400); font-size: 13px;">
                                                <i class="fas fa-circle-info"></i> Belum diunggah
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-actions" data-label="Aksi">
                                        <?php if ($row['status'] == 'Menunggu Konfirmasi Pembayaran'): ?>
                                            <form method="POST" class="cell-actions">
                                                <input type="hidden" name="peminjaman_id" value="<?php echo (int)$row['id']; ?>">
                                                <input type="hidden" name="new_status" value="Siap Diambil">
                                                <button type="submit" name="update_status" class="btn-brand btn-sm-action">
                                                    <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="app-footer">
        <div class="app-footer-inner">
            <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat. All Rights Reserved.</p>
            <p>Portal Bendahara</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        if (window.jQuery && $.fn.DataTable && $('#orderTable').length) {
            $(document).ready(function () {
                $('#orderTable').DataTable({
                    searching: true,
                    paging: true,
                    ordering: true,
                    info: true,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ entri",
                        info: "Menampilkan _START_–_END_ dari _TOTAL_ antrean",
                        infoEmpty: "Tidak ada pembayaran menunggu konfirmasi",
                        emptyTable: "Semua pembayaran telah terverifikasi.",
                        paginate: {
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });
            });
        }

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
