<?php
include('styles.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Peminjam Alat') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat"; // Update to the new database name
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'peminjaman_id' is in the URL
if (!isset($_GET['peminjaman_id'])) {
    echo "Peminjaman ID is missing from the URL.";
    exit;
}

$peminjaman_id = $_GET['peminjaman_id']; // Mendapatkan ID peminjaman dari URL

// Mengambil detail peminjaman berdasarkan peminjaman_id
$peminjaman_sql = "SELECT p.id, p.total_amount, p.status, p.created_at, p.created_at AS peminjaman_date, dp.return_at
                   FROM peminjaman p
                   JOiN detail_peminjaman dp ON dp.peminjaman_id = p.id
                   WHERE p.id = ?";
$stmt_peminjaman = $conn->prepare($peminjaman_sql);
$stmt_peminjaman->bind_param("i", $peminjaman_id);
$stmt_peminjaman->execute();
$peminjaman_result = $stmt_peminjaman->get_result();

// Mengambil data detail peminjaman dengan informasi item
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Details - AS Berkah E-Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>

        .content {
            margin-top: 30px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th,
        .cart-table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .cart-table th {
            background-color: #ff7043;
            color: white;
        }

        .cart-table td {
            text-align: right;
        }

        .cart-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff4e6;
            border-radius: 8px;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
        }

    </style>
</head>
<body>


<div class="container">
    <div class="content">
        <h2>Peminjaman ID: <?php echo $peminjaman['id']; ?></h2>
        <p><strong>Status:</strong> <?php echo ucfirst($peminjaman['status']); ?></p>
        <p><strong>Tanggal Peminjaman:</strong> <?php echo $peminjaman['peminjaman_date']; ?></p>
        <p><strong>Tanggal Pengembalian:</strong> <?php echo $peminjaman['return_at']; ?></p>

        <h3>Order Items</h3>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Weekly Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_peminjaman_amount = 0;
                while ($item = $peminjaman_items_result->fetch_assoc()) {
                    $weeks = ceil((strtotime($item['return_at']) - strtotime($peminjaman['peminjaman_date'])) / (7 * 24 * 60 * 60));
                    $weekly_price = $item['quantity'] * $item['item_price'];
                    $total_item_price = $weekly_price * $weeks;
                    $total_peminjaman_amount += $total_item_price;

                    echo "<tr>
                            <td>{$item['item_id']}</td>
                            <td>{$item['item_name']}</td>
                            <td>{$item['quantity']}</td>
                            <td>Rp " . number_format($item['item_price'], 0, ',', '.') . "</td>
                            <td>Rp " . number_format($weekly_price, 0, ',', '.') . "</td>
                            <td>Rp " . number_format($total_item_price, 0, ',', '.') . "</td>
                          </tr>";
                }
?>
            </tbody>
        </table>

        <div class="cart-summary">
    <p class="total-price">Total Biaya Peminjaman: Rp
        <?php echo number_format($total_peminjaman_amount, 0, ',', '.'); ?>
    </p>
</div>

    </div>
</div>


</body>

</html>

<?php
$conn->close();
?>
