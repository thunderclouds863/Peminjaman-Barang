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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Peminjaman Alat</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
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
    margin-bottom: 180px;
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
    margin: 20px auto;
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
    font-size: 15px;
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
    bottom: 0;
    width: 100%;
}

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

/* Agar tabel dapat digulir pada layar kecil */
.table-responsive {
    overflow-x: auto;
}

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
    <h2><i class="fas fa-list"></i> Available Products</h2>
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search products...">
    </div>
    <table id="peminjamanTable">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Alat</th>
                <th>Deskripsi</th>
                <th>Harga</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to fetch item data
            $sql = "SELECT * FROM item ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr class='product-row'>
                            <td>" . $no++ . "</td>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                            <td>Rp " . number_format($row['price'], 0, ',', '.') . "</td>
                            <td>
                                <button class='action-button' onclick='addToCart(" . $row['id'] . ")'>
                                    <i class='fas fa-cart-plus'></i> Add to Cart
                                </button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No items found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

    <footer>
        <p>&copy; 2024 <i class="fas fa-cogs"></i> Sistem Informasi Peminjaman Alat</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        // JavaScript to handle the search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('#peminjamanTable .product-row');

    rows.forEach(function(row) {
        var name = row.querySelector('td:nth-child(2)').textContent.toLowerCase(); // Nama Alat column
        var description = row.querySelector('td:nth-child(3)').textContent.toLowerCase(); // Deskripsi column

        // Check if either name or description matches the search filter
        if (name.includes(filter) || description.includes(filter)) {
            row.style.display = ''; // Show row
        } else {
            row.style.display = 'none'; // Hide row
        }
    });
});
                function addToCart(itemId) {
            // Redirect to shopping-cart.php with the item_id to add it to the cart
            window.location.href = "shopping-cart.php?add_to_cart=" + itemId;
        }

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

<?php
$conn->close();
?>
