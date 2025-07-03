<?php
session_start();
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
            margin-bottom: 90px;
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
            margin-bottom: 80px;
            margin-top: 80px;
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
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #5a9e97;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
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
            background-color: #5a9e97 !important;
            color: white !important;
            text-align: center !important;
            text-transform: uppercase !important;
            font-weight: bold !important;
            padding: 15px !important;
        }

        thead {
            text-align: center;
        }

        td {
            padding: 12px;
            color: #555;
        }

        tr {
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

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
            margin-left: auto;
        }

        .search-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-bar input {
            width: 60%;
            max-width: 500px;
            padding: 10px;
            border-radius: 25px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .search-bar input::placeholder {
            color: #999;
            text-align: center;
        }

        th,
        td {
            padding: 15px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        tr {
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

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

    <footer>
        <p>&copy; 2024 Sistem Peminjaman Alat. All Rights Reserved.</p>
    </footer>

    <script>
        const hamburger = document.getElementById('hamburger');
        const navbar = document.getElementById('navbar');

        hamburger.addEventListener('click', () => {
            navbar.classList.toggle('active');
        });

        $(document).ready(function() {
            $('#myTable').DataTable();
        });
    </script>
</body>

</html>

