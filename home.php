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
if ($_SERVER['REQUEST_METHOD'] == 'POST'&& $_POST['action'] != 'bayar') {
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
}else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'bayar') {
    // Ambil data dari form
    $peminjaman_id = $_POST['peminjaman_id'];  // Ambil ID peminjaman dari form
    $metode_pembayaran = $_POST['payment_method']; // Ambil metode pembayaran
    $payment_proof = $_FILES['payment_proof']; // Ambil bukti pembayaran yang di-upload

    // Cek apakah file di-upload tanpa error
    if ($payment_proof['error'] == UPLOAD_ERR_OK) {
        // Tentukan direktori untuk menyimpan file
        $upload_dir = 'uploads/';

        // Ambil ekstensi file yang di-upload
        $file_extension = pathinfo($payment_proof['name'], PATHINFO_EXTENSION);

        // Tentukan nama file baru berdasarkan ID peminjaman dan ekstensi file
        $new_file_name = 'bukti' . $peminjaman_id . '.' . $file_extension;

        // Tentukan path lengkap untuk file yang akan di-upload
        $upload_path = $upload_dir . $new_file_name;

        // Pindahkan file ke direktori uploads
        if (move_uploaded_file($payment_proof['tmp_name'], $upload_path)) {


    // Logika untuk mencatat metode pembayaran, jika perlu
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
    <title>Sistem Informasi Peminjaman Alat</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
 html {
    height: 100%;
    margin-bottom: 25%;
    padding: 0;
    overflow-y: auto;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(45deg, #a0d9d2, #72c3b1, #63a6b1, #e1e8ee);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    padding: 0;
    align-items: center;
    margin-bottom: 190px;
    margin-top:80px;
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

header {
    position: fixed;       /* Menjadikan header tetap berada di atas saat halaman di-scroll */
    top: 0;                /* Menetapkan posisi di bagian atas halaman */
    left: 0;
    width: 100%;           /* Agar header lebar penuh */
    background-color: #5a9e97; /* Warna latar belakang header */
    color: white;          /* Warna teks pada header */
    padding: 15px 20px;    /* Padding di dalam header */
    display: flex;         /* Menggunakan Flexbox untuk pengaturan tata letak */
    justify-content: space-between; /* Menyebarkan elemen di dalam header */
    align-items: center;   /* Menyelaraskan elemen secara vertikal */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Efek bayangan pada header */
    z-index: 1000;         /* Memastikan header tetap berada di atas konten lain */
}

        header .logo a {
            text-decoration: none;
            color: white;
        }

        header .logo img {
            height: 40px;
            margin-right: 10px;
        }

        header .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        header .navbar a:hover {
            color: #ff7043;
            text-decoration: underline;
        }

        .container {
            width: 100%;
            margin-bottom:120px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .action-button {
        background-color: #ff7043;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .action-button:hover {
        background-color: #ff5722;
        transform: scale(1.1);
    }

        th {
    background-color: #5a9e97 !important;  /* Desired background color */
    color: white !important;  /* White text for contrast */
    text-align: center !important;  /* Center text within each header cell */
    text-transform: uppercase !important;  /* Uppercase for header text */
    font-weight: bold !important;  /* Bold text for emphasis */
    padding: 15px !important;  /* Ensure padding remains consistent */
}

/* Center the entire table title (Header section) */
thead {
    text-align: center; /* Ensures that all the header elements are centered */
}


    td {
        padding: 12px;
        color: #555;
    }

    tr {
        border-bottom: 1px solid #ddd;
    }

    tr:hover {
        background-color: #f1f1f1; /* Efek hover */
    }


/* Update table background color */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    text-align: center;
    background-color: #e1e8ee;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Adjust space between table and search options */
.dataTables_wrapper {
    margin-top: 20px; /* Add space between the table and the search */
}

/* Modify pagination and length menu spacing */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 10px; /* Adds space between filter/search and table */
}


        .floating-icons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .floating-icons i {
            font-size: 40px;
            margin: 10px;
            color: #ff7043;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .floating-icons i:hover {
            transform: scale(1.2);
        }

        footer {
            background-color: #5a9e97;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            margin-top:100px;
            bottom: 0;
            width: 100%;
        }

        /* Untuk tombol hamburger di sebelah kanan */
.hamburger {
    display: none;
    font-size: 24px;
    color: white;
    background: none;
    border: none;
    cursor: pointer;
    margin-left: auto; /* Memastikan hamburger di sebelah kanan */
}

.search-bar {
    display: flex;
    justify-content: center; /* Pusatkan secara horizontal */
    align-items: center;    /* Opsional, untuk memastikan elemen lain di dalamnya selaras */
    margin-bottom: 20px;    /* Jarak bawah dari elemen lain */
}

.search-bar input {
    width: 60%;
    max-width: 500px; /* Batasi lebar maksimal untuk input */
    padding: 10px;
    border-radius: 25px;
    border: 1px solid #ccc;
    outline: none;
    font-size: 14px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    text-align: center; /* Text di input juga berada di tengah */
}

.search-bar input::placeholder {
    color: #999;
    text-align: center;
}

th, td {
    padding: 15px; /* Menambahkan spasi dalam tabel */
}

/* Agar tabel dapat digulir pada layar kecil */
.table-responsive {
    overflow-x: auto;
}

/* Untuk menambah margin dan spasi */
tr {
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background-color: #f1f1f1; /* Efek hover */
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    header {
        flex-direction: column;
        align-items: flex-start;
    }

    .navbar {
        display: none;
        flex-direction: column;
        width: 100%;
        background-color: #5a9e97;
    }

    .navbar a {
        padding: 10px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .navbar a:last-child {
        border-bottom: none;
    }

    .hamburger {
        display: block;
        align-items: flex-end;
    }

    .navbar.active {
        display: flex;
    }
}

    </style>
</head>

<body>
<header>
    <div class="logo">
        <a href="home.php" style="display: flex; align-items: center; text-decoration: none;">
            <img src="logo.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            <h1 style="font-size: 18px; font-weight: 600; color: white;">Sistem Informasi Peminjaman Alat</h1>
        </a>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Toggle Navigation">
        <i class="fas fa-bars"></i>
    </button>
    <nav class="navbar" id="navbar">
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="peminjam_dashboard.php"><i class="fas fa-box"></i> Items</a>
        <a href="order-history.php"><i class="fas fa-history"></i> History Peminjaman</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>


<div class="container">
    <h2><i class="fas fa-box"></i> Dashboard Peminjaman</h2>
    <div class="search-bar">
    <i class="fas fa-search"></i>
    <input type="text" id="searchInput" placeholder="Search products...">
</div>
    <div class="table-responsive"> <!-- Membuat tabel responsif -->
        <table id="peminjamanTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>View Detail</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
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
                        WHERE p.user_id = ? AND p.status IN ('Pending','Dikonfirmasi','Menunggu Pembayaran','Menunggu Konfirmasi Pembayaran','Pembayaran Dikonfirmasi','Siap Diambil','Sedang Dipinjam','Menunggu Konfirmasi Pengembalian','Pengembalian Dikonfirmasi')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $no = 1;
                $total_pembayaran = 0;
                while ($row = $result->fetch_assoc()) {
                    // $total_pembayaran = $row['total_pembayaran']; // Mengakumulasi total pembayaran
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['borrow_date']; ?></td>
                        <td><?php echo $row['return_date']; ?></td>
                        <td><?php echo ucfirst($row['loan_status']); ?></td>
                        <td>
    <a href="order-details.php?peminjaman_id=<?php echo $row['peminjaman_id']; ?>" class="action-button" style="text-decoration: none; display: inline-block;">Detail</a>
</td>


                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="peminjaman_id" value="<?php echo $row['peminjaman_id']; ?>">
                                <?php if ($row['loan_status'] == 'Dikonfirmasi') { ?>
            <button type="button" class="action-button bayarBtn" data-peminjaman-id="<?php echo $row['peminjaman_id']; ?>" data-bs-toggle="modal" data-bs-target="#paymentModal">Bayar</button>
        <?php } elseif ($row['loan_status'] == 'Siap Diambil') { ?>
                                    <button type="submit" name="action" value="Ambil" class="action-button">Ambil Barang</button>
                                <?php } elseif ($row['loan_status'] == 'Sedang Dipinjam') { ?>
                                    <button type="submit" name="action" value="kembali" class="action-button">Return</button>
                                <?php } elseif ($row['loan_status'] == 'Pengembalian Dikonfirmasi') { ?>
                                    <button type="submit" name="action" value="selesai" class="action-button">Selesai</button>
                                <?php } ?>
                            </form>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div> <!-- End table-responsive -->
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <?php
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
                        WHERE p.user_id = ? AND p.status IN ('Pending','Dikonfirmasi','Menunggu Pembayaran','Menunggu Konfirmasi Pembayaran','Pembayaran Dikonfirmasi','Siap Diambil','Sedang Dipinjam','Menunggu Konfirmasi Pengembalian','Pengembalian Dikonfirmasi')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $no = 1;
                $total_pembayaran = 0;
                if ($row = $result->fetch_assoc()) {
                    $total_pembayaran = $row['total_pembayaran']; // Mengakumulasi total pembayaran
                ?>
            <p class="total-price">Total Biaya Peminjaman: Rp
    <?php echo number_format($total_pembayaran, 0, ',', '.'); ?>
</p>
<?php
                }
                ?>
<form id="paymentForm" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="peminjaman_id" id="modalPeminjamanId">

    <div class="mb-3">
        <label for="paymentMethod" class="form-label">Pilih Metode Pembayaran</label>
        <select class="form-select" id="paymentMethod" name="payment_method" required>
            <option value="Bank Mandiri - 1090020254033 / Ferry Anugerah">Bank Mandiri (1090020254033) / Ferry Anugerah</option>
            <option value="Bank BNI - 1905760900 / Ferry Anugerah">Bank BNI (1905760900) / Ferry Anugerah</option>
            <option value="ShopeePay - 081214809536 / Ferry Anugerah">ShopeePay (081214809536) / Ferry Anugerah</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="paymentProof" class="form-label">Upload Bukti Pembayaran</label>
        <input type="file" class="form-control" id="paymentProof" name="payment_proof" required>
    </div>

    <button type="submit" name="action" value="bayar" class="btn btn-primary w-100">Bayar Sekarang</button>
</form>

            </div>
        </div>
    </div>
</div>


<footer>
    <p>&copy; 2024 Sistem Peminjaman Alat. All Rights Reserved.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    // JavaScript to handle the search functionality
    document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase(); // Ambil input pencarian dan ubah menjadi huruf kecil
    const tableRows = document.querySelectorAll('#peminjamanTable tbody tr'); // Ambil semua baris di tabel

    tableRows.forEach(row => {
        const rowText = row.textContent.toLowerCase(); // Gabungkan semua teks dalam baris menjadi huruf kecil
        if (rowText.includes(searchValue)) {
            row.style.display = ''; // Tampilkan baris jika cocok
        } else {
            row.style.display = 'none'; // Sembunyikan baris jika tidak cocok
        }
    });
});

    document.addEventListener('DOMContentLoaded', function () {
    // Atur modal pembayaran
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));

    // Ketika tombol bayar diklik
    document.querySelectorAll('.bayarBtn').forEach(button => {
        button.addEventListener('click', function () {
            const peminjamanId = this.dataset.peminjamanId; // Ambil ID dari atribut data-peminjaman-id
            document.getElementById('modalPeminjamanId').value = peminjamanId; // Setel ID ke input form modal
        });
    });
});

   $(document).ready(function() {
    const table = $('#peminjamanTable').DataTable({
        searching: false, // Menonaktifkan search bar bawaan DataTables
        "language": {
            "lengthMenu": "Tampilkan _MENU_ entri",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "paginate": {
                "next": "Berikutnya",
                "previous": "Sebelumnya"
            }
        }
    });

    // Implementasi search bar kustom
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
});

    const hamburger = document.getElementById('hamburger');
    const navbar = document.getElementById('navbar');

    hamburger.addEventListener('click', () => {
        navbar.classList.toggle('active');
    });
</script>

</body>
</html>
