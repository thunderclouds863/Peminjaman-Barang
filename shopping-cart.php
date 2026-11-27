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

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_GET['add_to_cart'])) {
    $item_id = intval($_GET['add_to_cart']);
    if ($item_id > 0) {
        if (!isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id] = ['quantity' => 0];
        }
        $_SESSION['cart'][$item_id]['quantity']++;
    }
}

// Handle remove from cart
if (isset($_GET['remove_from_cart'])) {
    $item_id = intval($_GET['remove_from_cart']);
    if (isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
    }
}

// Handle quantity change
if (isset($_POST['update_quantity'])) {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $item_id => $quantity) {
            if (isset($_SESSION['cart'][$item_id])) {
                $quantity = max(1, min(10, intval($quantity)));
                $_SESSION['cart'][$item_id]['quantity'] = $quantity;
            }
        }
    }
}

// Resolve cart items for display & summary
$resolved_items = [];
$subtotal_weekly = 0;
$total_units = 0;

foreach ($_SESSION['cart'] as $item_id => $item) {
    if ($item_id <= 0) {
        unset($_SESSION['cart'][$item_id]);
        continue;
    }
    if (is_array($item) && isset($item['quantity'])) {
        $sql = "SELECT * FROM item WHERE id = " . intval($item_id);
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $line_total = $product['price'] * $item['quantity'];
            $subtotal_weekly += $line_total;
            $total_units += (int)$item['quantity'];
            $resolved_items[] = [
                'id' => $item_id,
                'name' => $product['name'],
                'description' => $product['description'] ?? '',
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
    <title>Keranjang Peminjaman — D'Tools</title>
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
            <h1 class="page-title">Keranjang Peminjaman</h1>
            <div class="page-actions">
                <a href="peminjam_dashboard.php" class="btn-outline-subtle">
                    <i class="fas fa-arrow-left"></i> Katalog Alat
                </a>
            </div>
        </div>

        <div class="checkout-steps" aria-label="Tahapan Peminjaman">
            <div class="checkout-step done"><span class="step-dot"><i class="fas fa-check"></i></span> Pilih Alat</div>
            <span class="step-sep"></span>
            <div class="checkout-step active"><span class="step-dot">2</span> Keranjang</div>
            <span class="step-sep"></span>
            <div class="checkout-step"><span class="step-dot">3</span> Checkout</div>
        </div>

        <div class="split-layout">
            <section class="surface-card">
                <form method="POST">
                    <?php if (empty($resolved_items)): ?>
                        <div class="empty-state">
                            <img src="assets/svg/empty-cart.svg" alt="" class="empty-state-svg" aria-hidden="true">
                            <h3>Keranjang Peminjaman Kosong</h3>
                            <p>Anda belum memilih alat laboratorium untuk dipinjam.</p>
                            <a href="peminjam_dashboard.php" class="btn-brand">
                                <i class="fas fa-boxes-stacked"></i> Jelajahi Katalog Alat
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="cart-list">
                            <?php foreach ($resolved_items as $entry): ?>
                                <div class="cart-row" id="cart-item-<?php echo $entry['id']; ?>">
                                    <div class="cart-row-info">
                                        <h3><?php echo htmlspecialchars($entry['name']); ?></h3>
                                        <p class="cart-row-price">
                                            Rp <?php echo number_format($entry['price'], 0, ',', '.'); ?> / minggu &times; <?php echo (int)$entry['quantity']; ?>
                                            &nbsp;&bull;&nbsp;
                                            <strong style="color: var(--ink-950);">Rp <?php echo number_format($entry['line_total'], 0, ',', '.'); ?></strong>
                                        </p>
                                    </div>

                                    <div class="cart-row-controls">
                                        <div class="qty-stepper">
                                            <button type="button" class="quantity-button decrease" data-item-id="<?php echo $entry['id']; ?>" aria-label="Kurangi jumlah">&minus;</button>
                                            <input type="number" name="quantity[<?php echo $entry['id']; ?>]" value="<?php echo (int)$entry['quantity']; ?>" min="1" max="10" class="quantity-input" aria-label="Jumlah">
                                            <button type="button" class="quantity-button increase" data-item-id="<?php echo $entry['id']; ?>" aria-label="Tambah jumlah">+</button>
                                        </div>

                                        <a href="shopping-cart.php?remove_from_cart=<?php echo $entry['id']; ?>"
                                           class="btn-soft-danger btn-sm-action"
                                           onclick="return confirm('Hapus alat ini dari keranjang?');">
                                            <i class="fas fa-trash-can"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </section>

            <aside class="surface-card">
                <div class="surface-card-header">
                    <h2 class="surface-card-title"><i class="fas fa-receipt"></i> Ringkasan Estimasi</h2>
                </div>
                <div class="surface-card-body">
                    <div class="summary-line">
                        <span>Jenis Alat</span>
                        <span class="cell-mono"><?php echo count($resolved_items); ?> item</span>
                    </div>
                    <div class="summary-line">
                        <span>Total Unit</span>
                        <span class="cell-mono"><?php echo $total_units; ?> unit</span>
                    </div>
                    <div class="summary-total">
                        <span>Estimasi / Minggu</span>
                        <span class="mono-amount">Rp <?php echo number_format($subtotal_weekly, 0, ',', '.'); ?></span>
                    </div>
                    <p style="font-size: 12.5px; color: var(--ink-500); margin: 12px 0 18px;">
                        Total biaya akhir dihitung otomatis berdasarkan jumlah minggu pada halaman checkout.
                    </p>
                    <?php if (!empty($resolved_items)): ?>
                        <a href="checkout.php" class="btn-brand" style="width: 100%; padding: 12px 16px;">
                            <span>Pinjam Sekarang</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="peminjam_dashboard.php" class="btn-outline-subtle" style="width: 100%; padding: 12px 16px;">
                            <i class="fas fa-boxes-stacked"></i> Pilih Alat Dulu
                        </a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </main>

    <footer class="app-footer">
        <div class="app-footer-inner">
            <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat.</p>
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
        const decreaseButtons = document.querySelectorAll('.decrease');
        const increaseButtons = document.querySelectorAll('.increase');

        decreaseButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const itemId = this.getAttribute('data-item-id');
                const input = document.querySelector(`input[name="quantity[${itemId}]"]`);
                const currentValue = parseInt(input.value, 10);
                if (currentValue > 1) {
                    const nextVal = currentValue - 1;
                    input.value = nextVal;
                    updateCart(itemId, nextVal);
                }
            });
        });

        increaseButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const itemId = this.getAttribute('data-item-id');
                const input = document.querySelector(`input[name="quantity[${itemId}]"]`);
                const currentValue = parseInt(input.value, 10);
                if (currentValue < 10) {
                    const nextVal = currentValue + 1;
                    input.value = nextVal;
                    updateCart(itemId, nextVal);
                }
            });
        });

        function updateCart(itemId, newQuantity) {
            const formData = new FormData();
            formData.append('update_quantity', true);
            formData.append(`quantity[${itemId}]`, newQuantity);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload();
            })
            .catch(error => console.error('Error:', error));
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
