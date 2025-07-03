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

        // Update status peminjaman
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
        // Ambil ID peminjaman dari form
        $peminjaman_id = $_POST['peminjaman_id'];
        $payment_proof = $_FILES['payment_proof'];

        // Tentukan direktori untuk upload file
        $upload_dir = 'uploads/';
        $file_tmp = $payment_proof['tmp_name'];
        $file_extension = pathinfo($payment_proof['name'], PATHINFO_EXTENSION);

        // Tentukan nama file baru berdasarkan ID peminjaman
        $new_file_name = 'bukti' . $peminjaman_id . '.' . $file_extension;
        $upload_path = $upload_dir . $new_file_name;

        // Cek apakah file berhasil di-upload
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Update database dengan nama file bukti pembayaran yang baru
            $update_payment_query = "UPDATE peminjaman SET payment_proof='$new_file_name' WHERE id=$peminjaman_id";
            if (mysqli_query($conn, $update_payment_query)) {
                // Redirect ke halaman dashboard setelah berhasil
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bendahara</title>

    <!-- Include DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* Global body styling with gradient background */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #a5d8a5, #c8e1e0); /* Pastel green-blue gradient */
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 0;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Header Styling */
        .header {
            background-color: #4fa3a1;
            color: white;
            padding: 15px;
            text-align: center;
            position: relative;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); /* Shadow effect for text */
            font-size: 2.5rem;
        }

        .logout-btn {
            position: absolute;
            top: 10px;
            right: 20px;
            background-color: #f48fb1;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #f06292;
        }

        /* Container Styling */
        .container {
            width: 85%;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            font-size:30px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4fa3a1; /* Slightly darker for contrast */
            color: white;
        }

        td {
            background-color: #f1f1f1;
        }

        /* Button Styling */
        button {
            padding: 5px 10px;
            background: linear-gradient(135deg, #8fd9a8, #70c5b0);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #70c5b0, #8fd9a8);
        }

        /* Footer Styling */
        footer {
            background-color: #4fa3a1;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
        }

        /* Styling for file link */
        a {
            color: #3498db;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

    </style>
</head>

<body>

    <div class="header">
        <h2>Dashboard Bendahara</h2>
        <a href="?logout=true" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="container">
        <h2>Peminjaman untuk Diproses</h2>

        <table id="orderTable">
            <thead>
                <tr>
                    <th>ID Peminjaman</th>
                    <th>Status</th>
                    <th>Bukti Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <?php
                            // Tentukan direktori tempat file disimpan
                            $upload_dir = 'uploads/';

                            // Ambil ID peminjaman dari database atau variabel
                            $peminjaman_id = $row['id']; // ID peminjaman diambil dari database

                            // Tentukan daftar ekstensi yang diizinkan
                            $allowed_extensions = ['png', 'jpg', 'jpeg'];

                            // Cek apakah ada file yang diunggah dan tentukan ekstensi file
                            $file_found = false;
                            foreach ($allowed_extensions as $ext) {
                                $file_name = 'bukti' . $peminjaman_id . '.' . $ext;
                                $file_path = $upload_dir . $file_name;

                                if (file_exists($file_path)) {
                                    $file_found = true;
                                    break; // Jika file ditemukan, hentikan pencarian
                                }
                            }

                            if ($file_found):
                            ?>
                                <!-- Link untuk bukti pembayaran jika file ditemukan -->
                                <a href="<?php echo htmlspecialchars($file_path, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">Lihat Bukti Pembayaran</a>
                            <?php else: ?>
                                <p>Bukti pembayaran belum di-upload.</p>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($row['status'] == 'Menunggu Konfirmasi Pembayaran'): ?>
                                <form method="POST" class="status-buttons">
                                    <input type="hidden" name="peminjaman_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="new_status" value="Siap Diambil">
                                    <button type="submit" name="update_status">Konfirmasi Pembayaran</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; 2024 Sistem Peminjaman Alat. All rights reserved.</p>
    </footer>

    <script>
        $(document).ready(function() {
            $('#orderTable').DataTable({
                "searching": true,
                "paging": true,
                "ordering": true,
                "info": false
            });
        });
    </script>

</body>

</html>

<?php
$conn->close();
?>
