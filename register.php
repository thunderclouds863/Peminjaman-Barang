<?php
session_start();
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

// Handle form submission for registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check if username already exists
    $sql_check = "SELECT * FROM users WHERE username = '$username'";
    $result_check = $conn->query($sql_check);
    if ($result_check->num_rows > 0) {
        header("Location: register.php?message=Username already exists!&type=error");
        exit();
    }

    // Insert the new user into the database
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?message=Registration successful!");
    } else {
        header("Location: register.php?message=Error: " . $conn->error . "&type=error");
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun — D'Tools Sistem Informasi Peminjaman Alat</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css">
</head>

<body class="auth-body">
  <main class="auth-shell">
    <section class="auth-brand-panel" aria-label="Pendaftaran D'Tools">
      <div class="auth-brand-content">
        <div class="auth-logo-badge">
          <img src="logo.png" alt="Logo D'Tools">
          <span>D'Tools Inventory</span>
        </div>

        <h1>Buat Akun Peminjam Alat</h1>
        <p class="auth-lead">
          Daftarkan akun Anda untuk mulai mengajukan peminjaman peralatan laboratorium, melihat riwayat transaksi, dan memantau status pengembalian.
        </p>

        <ul class="auth-steps">
          <li>
            <span class="auth-step-icon"><i class="fas fa-user-plus"></i></span>
            <span><strong>Registrasi Instan</strong> — Buat akun Peminjam Alat dalam beberapa detik.</span>
          </li>
          <li>
            <span class="auth-step-icon"><i class="fas fa-cart-shopping"></i></span>
            <span><strong>Keranjang Peminjaman</strong> — Pilih beberapa alat sekaligus dan tentukan tanggal pengembalian.</span>
          </li>
          <li>
            <span class="auth-step-icon"><i class="fas fa-receipt"></i></span>
            <span><strong>Riwayat Transparan</strong> — Semua detail biaya dan status tercatat rapi di akun Anda.</span>
          </li>
        </ul>
      </div>

      <div class="auth-brand-footer">
        <span>D'Tools Lab Management</span>
        <span>Peminjam Portal</span>
      </div>
    </section>

    <section class="auth-form-panel">
      <div class="auth-mobile-logo">
        <img src="logo.png" alt="Logo D'Tools">
        <div class="brand-text">
          <span class="brand-title">D'Tools</span>
          <span class="brand-subtitle">Sistem Informasi Peminjaman Alat</span>
        </div>
      </div>

      <div class="auth-form-header">
        <h2>Registrasi Akun</h2>
        <p>Lengkapi data di bawah ini untuk mendaftar sebagai Peminjam Alat.</p>
      </div>

      <?php if (!empty($_GET['message'])): ?>
        <?php $msgType = ($_GET['type'] ?? 'success') === 'error' ? 'error' : 'success'; ?>
        <div class="alert-banner <?php echo $msgType; ?>" role="alert">
          <i class="fas <?php echo $msgType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
          <span><?php echo htmlspecialchars($_GET['message']); ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label-dt" for="role">Peran Pengguna</label>
          <div class="input-icon-wrap">
            <i class="fas fa-user-tag"></i>
            <select name="role" id="role" class="form-control-dt" required>
              <option value="" disabled selected>-- Pilih Role --</option>
              <option value="Peminjam Alat">Peminjam Alat</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label-dt" for="username">Username</label>
          <div class="input-icon-wrap">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" class="form-control-dt" placeholder="Buat username unik" autocomplete="username" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label-dt" for="password">Password</label>
          <div class="input-icon-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" class="form-control-dt" placeholder="Buat password" autocomplete="new-password" required>
          </div>
        </div>

        <button type="submit" name="register" value="register" class="btn-brand auth-submit-btn">
          <span>Daftar Sekarang</span>
          <i class="fas fa-user-check"></i>
        </button>
      </form>

      <div class="auth-switch-link">
        Sudah punya akun? <a href="index.php">Masuk di sini</a>
      </div>
    </section>
  </main>
</body>

</html>

<?php $conn->close(); ?>
