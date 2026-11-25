<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM item ORDER BY created_at DESC";
$result = $conn->query($sql);
$items = [];
$total_stock = 0;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
        $total_stock += (int)($row['stock'] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Alat — D'Tools Sistem Informasi Peminjaman Alat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
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
                <a href="peminjam_dashboard.php" class="active"><i class="fas fa-boxes-stacked"></i> Katalog Alat</a>
                <a href="shopping-cart.php"><i class="fas fa-cart-shopping"></i> Keranjang</a>
                <a href="order-history.php"><i class="fas fa-clock-rotate-left"></i> Riwayat</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="page-header">
            <div>
                <div class="page-eyebrow"><i class="fas fa-microscope"></i> Katalog Inventaris</div>
                <h1 class="page-title">Alat Tersedia untuk Dipinjam</h1>
                <p class="page-subtitle">Pilih peralatan laboratorium yang Anda butuhkan dan tambahkan ke keranjang peminjaman. Tarif dihitung per minggu.</p>
            </div>
            <div class="page-actions">
                <a href="shopping-cart.php" class="btn-brand">
                    <i class="fas fa-cart-shopping"></i> Lihat Keranjang
                </a>
            </div>
        </div>

        <section class="stats-grid" aria-label="Statistik Katalog">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Jenis Alat Tersedia</div>
                    <div class="stat-value"><?php echo count($items); ?></div>
                </div>
                <div class="stat-icon crimson"><i class="fas fa-boxes-stacked"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Unit di Gudang</div>
                    <div class="stat-value"><?php echo $total_stock; ?></div>
                </div>
                <div class="stat-icon emerald"><i class="fas fa-cubes"></i></div>
            </div>
        </section>

        <section class="surface-card">
            <div class="surface-card-header">
                <h2 class="surface-card-title"><i class="fas fa-list"></i> Daftar Peralatan</h2>
                <div class="search-field">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama alat atau deskripsi...">
                </div>
            </div>

            <div class="surface-card-body">
                <div class="dt-table-wrap">
                    <table id="peminjamanTable" class="dt-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Alat</th>
                                <th>Deskripsi</th>
                                <th>Stok</th>
                                <th>Harga / Minggu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($items) > 0) {
                                $no = 1;
                                foreach ($items as $row) {
                                    $stock = (int)($row['stock'] ?? 0);
                                    $stockClass = $stock <= 0 ? 'empty' : ($stock <= 2 ? 'low' : '');
                                    echo "<tr class='product-row'>
                                            <td class='cell-mono'>" . $no++ . "</td>
                                            <td class='cell-strong'>" . htmlspecialchars($row['name']) . "</td>
                                            <td>" . htmlspecialchars($row['description']) . "</td>
                                            <td><span class='stock-pill {$stockClass}'>{$stock} unit</span></td>
                                            <td class='cell-mono'>Rp " . number_format($row['price'], 0, ',', '.') . "</td>
                                            <td>
                                                <button type='button' class='btn-brand btn-sm-action' onclick='addToCart(" . (int)$row['id'] . ")'>
                                                    <i class='fas fa-cart-plus'></i> Tambah
                                                </button>
                                            </td>
                                          </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <footer class="app-footer">
        <div class="app-footer-inner">
            <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat.</p>
            <p>Tarif sewa dihitung per minggu</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        function addToCart(itemId) {
            window.location.href = "shopping-cart.php?add_to_cart=" + itemId;
        }

        $(document).ready(function () {
            const table = $('#peminjamanTable').DataTable({
                dom: 'lrtip',
                language: {
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ alat",
                    infoEmpty: "Tidak ada alat tersedia",
                    emptyTable: "Belum ada alat yang tersedia saat ini.",
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
