<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Divisi Alat Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-dyAg5H/ry4fvuKv5M0tUToE1OPnYl4dEeXZXJ3/ZIH3Jj3uT60/KFlhBRFI0VuA1RbZRCUSsO4OQ9xdZBC++qg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
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

    .container {
      text-align: center;
      padding: 50px;
    }

    h1 {
      font-size: 2.5em;
      color: rgba(0, 0, 0, 0.7);
    }

    p {
      font-size: 1.2em;
      color: rgba(0, 0, 0, 0.7);
      margin: 20px auto;
      max-width: 600px;
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
    <h1>Selamat Datang, Divisi Alat!</h1>
    <p>Sebagai Divisi Alat, Anda memiliki akses untuk mengelola inventaris, memantau peminjaman, dan memastikan alat tersedia dengan baik untuk peminjam. Silakan gunakan menu di atas untuk memulai tugas Anda.</p>
  </div>

  <footer>
    <p>&copy; 2024 Sistem Informasi Peminjaman Alat. All Rights Reserved.</p>
  </footer>
</body>

</html>
