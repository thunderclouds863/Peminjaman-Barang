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

$conn = demo_mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$cart_items = $_SESSION['cart'] ?? [];
$total_amount = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $return_at = $_POST['return_at'];

    if (empty($cart_items)) {
        echo "<p>Your cart is empty. Please add items to your cart before checkout.</p>";
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Insert order into peminjaman table
    $sql = "INSERT INTO peminjaman (user_id, total_amount, status) VALUES (?, 0, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $peminjaman_id = $stmt->insert_id;

        foreach ($cart_items as $item_id => $item) {
            $sql = "SELECT * FROM item WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $price = $product['price'];
                $quantity = $item['quantity'];

                $created_at = date("Y-m-d");
                $weeks = ceil((strtotime($return_at) - strtotime($created_at)) / (7 * 24 * 60 * 60));

                $total_price = $price * $quantity * $weeks;

                $sql = "INSERT INTO detail_peminjaman (peminjaman_id, item_id, quantity, price, return_at) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiids", $peminjaman_id, $item_id, $quantity, $price, $return_at);
                $stmt->execute();

                $total_amount += $total_price;
            }
        }

        $sql = "UPDATE peminjaman SET total_amount = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $total_amount, $peminjaman_id);
        $stmt->execute();

        $_SESSION['cart'] = [];
        header("Location: order-history.php");
        exit;
    }
}

$checkout_rows = [];
if (!empty($cart_items)) {
    foreach ($cart_items as $item_id => $item) {
        $sql = "SELECT * FROM item WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $line_total = $product['price'] * $item['quantity'];
            $total_amount += $line_total;
            $checkout_rows[] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $item['quantity'],
                'line_total' => $line_total,
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Peminjaman — D'Tools</title>
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
                <a href="shopping-cart.php" class="active"><i class="fas fa-cart-shopping"></i> Keranjang</a>
                <a href="order-history.php"><i class="fas fa-clock-rotate-left"></i> Riwayat</a>
                <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="page-header">
            <h1 class="page-title">Checkout</h1>
            <div class="page-actions">
                <a href="shopping-cart.php" class="btn-outline-subtle">
                    <i class="fas fa-arrow-left"></i> Keranjang
                </a>
            </div>
        </div>

        <div class="checkout-steps" aria-label="Tahapan Peminjaman">
            <div class="checkout-step done"><span class="step-dot"><i class="fas fa-check"></i></span> Pilih Alat</div>
            <span class="step-sep"></span>
            <div class="checkout-step done"><span class="step-dot"><i class="fas fa-check"></i></span> Keranjang</div>
            <span class="step-sep"></span>
            <div class="checkout-step active"><span class="step-dot">3</span> Checkout</div>
        </div>

        <div class="split-layout">
            <section class="surface-card">
                <?php if (!empty($checkout_rows)): ?>
                    <div class="cart-list">
                        <?php foreach ($checkout_rows as $item): ?>
                            <div class="cart-row">
                                <div class="cart-row-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="cart-row-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?> &times; <?php echo (int)$item['quantity']; ?> unit</p>
                                </div>
                                <div class="cell-mono" style="font-weight: 700;">
                                    Rp <?php echo number_format($item['line_total'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <img src="assets/svg/empty-cart.svg" alt="" class="empty-state-svg" aria-hidden="true">
                        <h3>Keranjang Kosong</h3>
                        <p>Tambahkan alat terlebih dahulu sebelum melakukan checkout.</p>
                        <a href="peminjam_dashboard.php" class="btn-brand">Lihat Katalog Alat</a>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="surface-card">
                <div class="surface-card-header">
                    <h2 class="surface-card-title"><i class="fas fa-calendar-days"></i> Jadwal Pengembalian</h2>
                </div>
                <div class="surface-card-body">
                    <div class="summary-line">
                        <span>Tarif Dasar / Minggu</span>
                        <span class="cell-mono">Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></span>
                    </div>

                    <form action="checkout.php" method="POST" style="margin-top: 16px;" data-weekly-base="<?php echo (float)$total_amount; ?>">
                        <div class="form-group">
                            <label for="return_at" class="form-label-dt">Pilih Tanggal Pengembalian</label>
                            <input type="date" id="return_at" name="return_at" class="form-control-dt" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>

                        <div class="live-estimate-box" id="liveEstimateBox">
                            <span id="liveEstimateLabel">Estimasi Total (1 minggu)</span>
                            <strong id="liveEstimateValue">Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></strong>
                        </div>

                        <p style="font-size: 12.5px; color: var(--ink-500); margin: 0 0 18px;">
                            Biaya sewa dihitung per kelipatan 7 hari sejak tanggal pengajuan hari ini.
                        </p>

                        <button type="submit" class="btn-brand" style="width: 100%; padding: 12px 16px;" <?php echo empty($checkout_rows) ? 'disabled' : ''; ?>>
                            <i class="fas fa-check-circle"></i> Ajukan Peminjaman
                        </button>
                    </form>
                </div>
            </aside>
        </div>
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
        <a href="shopping-cart.php" class="active"><i class="fas fa-cart-shopping"></i><span>Keranjang</span></a>
        <a href="order-history.php"><i class="fas fa-clock-rotate-left"></i><span>Riwayat</span></a>
    </nav>

    <script>
        const returnInput = document.getElementById('return_at');
        const checkoutForm = returnInput ? returnInput.closest('form') : null;
        const estLabel = document.getElementById('liveEstimateLabel');
        const estValue = document.getElementById('liveEstimateValue');

        if (returnInput && checkoutForm && estLabel && estValue) {
            const weeklyBase = parseFloat(checkoutForm.dataset.weeklyBase || '0') || 0;
            returnInput.addEventListener('change', function () {
                if (!this.value) return;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const chosen = new Date(this.value + 'T00:00:00');
                const diffDays = Math.max(1, Math.ceil((chosen - today) / (1000 * 60 * 60 * 24)));
                const weeks = Math.max(1, Math.ceil(diffDays / 7));
                const total = weeklyBase * weeks;
                estLabel.textContent = `Estimasi Total (${weeks} minggu)`;
                estValue.textContent = 'Rp ' + total.toLocaleString('id-ID');
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
