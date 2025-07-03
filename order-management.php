<?php
// session_start();
// if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Divisi Alat') {
//     header("Location: index.php");
//     exit();
// }

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Peminjaman Confirmation
if (isset($_GET['confirm_peminjaman'])) {
    $peminjaman_id = $_GET['confirm_peminjaman'];

    // Update peminjaman status to 'Dikonfirmasi'
    $update_status = "UPDATE peminjaman SET status = 'Dikonfirmasi' WHERE id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $peminjaman_id);
    $stmt->execute();

    // Decrease stock for each item in the peminjaman
    $peminjaman_items = "SELECT item_id, quantity FROM detail_peminjaman WHERE peminjaman_id = ?";
    $stmt = $conn->prepare($peminjaman_items);
    $stmt->bind_param("i", $peminjaman_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $item_id = $item['item_id'];
        $quantity = $item['quantity'];

        // Update item stock
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

    // Decrease stock for each item in the peminjaman
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

        // Update item stock
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Management - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <style>
             html {
    height: 100%;
    margin-bottom: 30%;
    padding: 0;
    overflow-y: auto;
}
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(90deg, #f9c9d0, #c6d8e4, #e1d8f1, #d3f2e1, #e5d5c0);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

    nav {
      background-color: rgba(0, 0, 0, 0.7);
      color: white;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    nav ul {
      display: flex;
      list-style: none;
      margin: 0;
      padding: 0;
    }

    nav ul li {
      margin: 0 10px;
    }

    nav ul li a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      padding: 5px 10px;
      display: flex;
      align-items: center;
      transition: background-color 0.3s ease;
    }

    nav ul li a i {
      margin-right: 5px;
    }

    nav ul li a:hover {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 5px;
    }
    footer {
      background-color: rgba(0, 0, 0, 0.7);
      color: white;
      text-align: center;
      padding: 10px;
      position: fixed;
      bottom: 0;
      width: 100%;
    }

        .container {
            width: 80%;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-table th, .order-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .order-table th {
            background-color: #f1f1f1;
            color: #333;
        }

        .order-table td .status {
            font-weight: bold;
            color: #4caf50;
        }
        .confirm-btn {
            background: linear-gradient(to right, #a8e063, #56ab2f);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.5s ease, transform 0.3s ease;
        }

        .confirm-btn:hover {
            background: linear-gradient(to right, #56ab2f, #a8e063);
      transform: translateY(-2px);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
        }

        .confirm-btn:active {
            transform: translateY(2px);
        }

        /* Center align the title of the table */
        .order-table th {
            text-align: center;
        }

    </style>
</head>
<body>
<nav>
  <div class="logo">
        <a href="home.php" style="display: flex; align-items: center; text-decoration: none;">
            <img src="logo.png" alt="Logo" style="height: 60px; margin-right: 10px;">
            <h1 style="font-size: 18px; font-weight: 600; color: white;">Divisi Alat</h1>
        </a>
    </div>
    <ul>
      <li><a href="divisi_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="inventory_management.php"><i class="fas fa-boxes"></i> Inventory</a></li>
      <li><a href="order-management.php"><i class="fas fa-list-alt"></i> Peminjaman</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>

  <div class="container">
        <h2 style="text-align: center; color: #333;">Peminjaman List</h2>
        <table id="orderTable" class="order-table">
            <thead>
                <tr>
                    <th>Peminjaman ID</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['username']}</td>
                                <td>{$row['created_at']}</td>
                                <td>Rp " . number_format($row['total_amount'], 0, ',', '.') . "</td>
                                <td><span class='status'>{$row['status']}</span></td>
                                <td>";
                        if ($row['status'] == 'Pending') {
                            echo "<a href='order-management.php?confirm_peminjaman={$row['id']}' class='confirm-btn'>Konfirmasi Peminjaman</a>";
                        } else if ($row['status'] == 'Menunggu Konfirmasi Pengembalian') {
                            echo "<a href='order-management.php?confirm_pengembalian={$row['id']}' class='confirm-btn'>Konfirmasi Pengembalian</a>";
                        }
                        echo "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No peminjaman found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <footer>
    <p>&copy; 2024 Sistem Peminjaman Alat. All Rights Reserved.</p>
</footer>
<script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#orderTable').DataTable();
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
