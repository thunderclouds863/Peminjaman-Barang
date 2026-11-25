<?php
session_start();

// Periksa peran pengguna
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Peminjam Alat') {
    header("Location: index.php");
    exit;
}

// Konfigurasi koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fungsi untuk memperbarui status peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] != 'bayar') {
    $peminjaman_id = $_POST['peminjaman_id'];
    $action = $_POST['action'];

    if ($action == 'Ambil') {
        $update_status = "UPDATE peminjaman SET status = 'Sedang Dipinjam' WHERE id = ?";
    } elseif ($action == 'kembali') {
        $update_status = "UPDATE peminjaman SET status = 'Menunggu Konfirmasi Pengembalian' WHERE id = ?";
    } elseif ($action == 'selesai') {
        $update_status = "UPDATE peminjaman SET status = 'Selesai' WHERE id = ?";
    }

    $stmt_update = $conn->prepare($update_status);
    $stmt_update->bind_param("i", $peminjaman_id);
    $stmt_update->execute();

    header("Location: home.php");
    exit();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'bayar') {
    // Ambil data dari form
    $peminjaman_id = $_POST['peminjaman_id'];
    $metode_pembayaran = $_POST['payment_method'];
    $payment_proof = $_FILES['payment_proof'];

    if ($payment_proof['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_extension = pathinfo($payment_proof['name'], PATHINFO_EXTENSION);
        $new_file_name = 'bukti' . $peminjaman_id . '.' . $file_extension;
        $upload_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($payment_proof['tmp_name'], $upload_path)) {
            $update_status = "UPDATE peminjaman SET status = 'Menunggu Konfirmasi Pembayaran', payment_method = ?, payment_proof = ? WHERE id = ?";
            $stmt_update = $conn->prepare($update_status);
            $stmt_update->bind_param("ssi", $metode_pembayaran, $file_data, $peminjaman_id);
            $stmt_update->execute();

            header("Location: home.php");
            exit();
        } else {
            echo "Failed to upload the file.";
        }
    } else {
        echo "Error uploading file.";
    }
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT
            p.id AS peminjaman_id,
            i.name AS item_name,
            i.description,
            d.quantity AS quantity,
            p.created_at AS borrow_date,
            d.return_at AS return_date,
            p.status AS loan_status,
            p.total_amount AS total_pembayaran
        FROM peminjaman p
        JOIN detail_peminjaman d ON p.id = d.peminjaman_id
        JOIN item i ON d.item_id = i.id
        WHERE p.user_id = ? AND p.status IN ('Pending','Dikonfirmasi','Menunggu Pembayaran','Menunggu Konfirmasi Pembayaran','Pembayaran Dikonfirmasi','Siap Diambil','Sedang Dipinjam','Menunggu Konfirmasi Pengembalian','Pengembalian Dikonfirmasi')
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$active_rows = [];
$count_borrowed = 0;
$count_action_needed = 0;
$first_total_pembayaran = 0;

while ($row = $result->fetch_assoc()) {
    $active_rows[] = $row;
    if ($first_total_pembayaran === 0) {
        $first_total_pembayaran = $row['total_pembayaran'];
    }
    if ($row['loan_status'] === 'Sedang Dipinjam') {
        $count_borrowed++;
    }
    if (in_array($row['loan_status'], ['Dikonfirmasi', 'Siap Diambil', 'Pengembalian Dikonfirmasi'], true)) {
        $count_action_needed++;
    }
}

function dt_status_class($status) {
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
    <title>Dashboard Peminjaman — D'Tools</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <a href="home.php" class="active"><i class="fas fa-house"></i> Home</a>
                <a href="peminjam_dashboard.php"><i class="fas fa-boxes-stacked"></i> Katalog Alat</a>
                <a href="shopping-cart.php"><i class="fas fa-cart-shopping"></i> Keranjang</a>
                <a href="order-history.php"><i class="fas fa-clock-rotate-left"></i> Riwayat</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="page-header">
            <h1 class="page-title">Dashboard Peminjaman</h1>
            <div class="page-actions">
                <a href="shopping-cart.php" class="btn-outline-subtle">
                    <i class="fas fa-cart-shopping"></i> Keranjang
                </a>
                <a href="peminjam_dashboard.php" class="btn-brand">
                    <i class="fas fa-plus"></i> Pinjam Alat
                </a>
            </div>
        </div>

        <div class="metric-strip" aria-label="Ringkasan Peminjaman">
            <div class="metric-chip"><span class="dot crimson"></span><strong><?php echo count($active_rows); ?></strong> Aktif</div>
            <div class="metric-chip"><span class="dot amber"></span><strong><?php echo $count_action_needed; ?></strong> Perlu Tindakan</div>
            <div class="metric-chip"><span class="dot blue"></span><strong><?php echo $count_borrowed; ?></strong> Sedang Dipinjam</div>
        </div>

        <section class="surface-card">
            <div class="surface-card-header">
                <div class="search-field" style="max-width: 380px;">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama alat atau status...">
                </div>
            </div>

            <div class="surface-card-body">
                <div class="dt-table-wrap">
                    <table id="peminjamanTable" class="dt-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Detail</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($active_rows as $row) {
                                $badgeClass = dt_status_class($row['loan_status']);
                            ?>
                                <tr>
                                    <td class="cell-mono"><?php echo $no++; ?></td>
                                    <td class="cell-strong"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td class="cell-mono"><?php echo (int)$row['quantity']; ?></td>
                                    <td class="cell-mono"><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                                    <td class="cell-mono"><?php echo htmlspecialchars($row['return_date']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst($row['loan_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?peminjaman_id=<?php echo $row['peminjaman_id']; ?>" class="btn-outline-subtle btn-sm-action">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                    <td>
                                        <form method="POST" class="cell-actions">
                                            <input type="hidden" name="peminjaman_id" value="<?php echo $row['peminjaman_id']; ?>">
                                            <?php if ($row['loan_status'] == 'Dikonfirmasi') { ?>
                                                <button type="button"
                                                    class="btn-brand btn-sm-action bayarBtn"
                                                    data-peminjaman-id="<?php echo $row['peminjaman_id']; ?>"
                                                    data-total-amount="<?php echo number_format($row['total_pembayaran'], 0, ',', '.'); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#paymentModal">
                                                    <i class="fas fa-credit-card"></i> Bayar
                                                </button>
                                            <?php } elseif ($row['loan_status'] == 'Siap Diambil') { ?>
                                                <button type="submit" name="action" value="Ambil" class="btn-brand btn-sm-action">
                                                    <i class="fas fa-hand-holding-box"></i> Ambil Barang
                                                </button>
                                            <?php } elseif ($row['loan_status'] == 'Sedang Dipinjam') { ?>
                                                <button type="submit" name="action" value="kembali" class="btn-dark-solid btn-sm-action">
                                                    <i class="fas fa-rotate-left"></i> Kembalikan
                                                </button>
                                            <?php } elseif ($row['loan_status'] == 'Pengembalian Dikonfirmasi') { ?>
                                                <button type="submit" name="action" value="selesai" class="btn-soft-success btn-sm-action">
                                                    <i class="fas fa-check"></i> Selesai
                                                </button>
                                            <?php } else { ?>
                                                <span style="color: var(--ink-400); font-size: 12.5px;">Menunggu proses</span>
                                            <?php } ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="fas fa-file-invoice-dollar" style="color: var(--brand-crimson); margin-right: 8px;"></i>
                        Konfirmasi Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="payment-total-box">
                        <span>Total Biaya Peminjaman</span>
                        <strong id="modalTotalAmount">Rp <?php echo number_format($first_total_pembayaran, 0, ',', '.'); ?></strong>
                    </div>

                    <form id="paymentForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="peminjaman_id" id="modalPeminjamanId">

                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label-dt">Pilih Metode Pembayaran</label>
                            <select class="form-control-dt" id="paymentMethod" name="payment_method" required>
                                <option value="Bank Mandiri - 1090020254033 / Ferry Anugerah">Bank Mandiri (1090020254033) / Ferry Anugerah</option>
                                <option value="Bank BNI - 1905760900 / Ferry Anugerah">Bank BNI (1905760900) / Ferry Anugerah</option>
                                <option value="ShopeePay - 081214809536 / Ferry Anugerah">ShopeePay (081214809536) / Ferry Anugerah</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="paymentProof" class="form-label-dt">Upload Bukti Pembayaran</label>
                            <input type="file" class="form-control" id="paymentProof" name="payment_proof" accept="image/*" required>
                        </div>

                        <button type="submit" name="action" value="bayar" class="btn-brand w-100" style="padding: 11px 16px;">
                            <i class="fas fa-paper-plane"></i> Kirim Bukti Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="app-footer">
        <div class="app-footer-inner">
            <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat. All Rights Reserved.</p>
            <p>Portal Peminjam</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.bayarBtn').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('modalPeminjamanId').value = this.dataset.peminjamanId;
                    if (this.dataset.totalAmount) {
                        document.getElementById('modalTotalAmount').textContent = 'Rp ' + this.dataset.totalAmount;
                    }
                });
            });
        });

        $(document).ready(function () {
            const table = $('#peminjamanTable').DataTable({
                dom: 'lrtip',
                language: {
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ peminjaman",
                    infoEmpty: "Belum ada peminjaman aktif",
                    emptyTable: "Belum ada peminjaman aktif saat ini.",
                    paginate: {
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            $('#searchInput').on('input', function () {
                table.search(this.value).draw();
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
