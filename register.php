<?php
session_start();
require_once __DIR__ . '/includes/auth_recovery.php';

$conn = dt_db_connect();
dt_ensure_recovery_schema($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    if ($role !== 'Peminjam Alat') {
        header("Location: register.php?message=" . urlencode("Role registrasi tidak valid.") . "&type=error");
        exit();
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?message=" . urlencode("Format alamat email tidak valid.") . "&type=error");
        exit();
    }

    if (strlen($password) < 6) {
        header("Location: register.php?message=" . urlencode("Password minimal 6 karakter.") . "&type=error");
        exit();
    }

    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR (email IS NOT NULL AND email = ?) LIMIT 1");
    $emailParam = $email !== '' ? $email : null;
    $stmt_check->bind_param("ss", $username, $emailParam);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: register.php?message=" . urlencode("Username atau email sudah terdaftar.") . "&type=error");
        exit();
    }

    $hashedPassword = dt_hash_password($password);
    $stmt_ins = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt_ins->bind_param("ssss", $username, $emailParam, $hashedPassword, $role);

    if ($stmt_ins->execute()) {
        header("Location: index.php?message=" . urlencode("Registrasi berhasil! Silakan masuk."));
        exit();
    } else {
        header("Location: register.php?message=" . urlencode("Gagal mendaftarkan akun. Silakan coba lagi.") . "&type=error");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi — D'Tools</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css?v=<?php echo filemtime('assets/css/theme.css'); ?>">
</head>

<body class="auth-stage">
  <header class="auth-topbar">
    <a href="index.php" class="auth-topbar-brand">
      <img src="logo.png" alt="D'Tools">
      <span>D'Tools</span>
    </a>
    <span class="auth-topbar-tag">Pendaftaran Peminjam</span>
  </header>

  <main class="auth-main">
    <section class="auth-panel" aria-labelledby="registerHeading">
      <div class="auth-panel-head">
        <div class="auth-logo-mark">
          <img src="logo.png" alt="Logo D'Tools">
        </div>
        <h1 id="registerHeading">Registrasi Akun</h1>
        <p class="auth-panel-sub">Buat akun peminjam alat laboratorium.</p>
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
          <label class="form-label-dt" for="role">Role</label>
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
            <input type="text" id="username" name="username" class="form-control-dt" placeholder="Pilih username" autocomplete="username" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label-dt" for="email">Email</label>
          <div class="input-icon-wrap">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" class="form-control-dt" placeholder="nama@dtools.lab" autocomplete="email" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label-dt" for="password">Password</label>
          <div class="input-icon-wrap has-toggle">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" class="form-control-dt" placeholder="Minimal 6 karakter" autocomplete="new-password" minlength="6" required>
            <button type="button" class="password-toggle-btn" data-toggle-password="password" aria-label="Tampilkan password">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" name="register" value="register" class="btn-brand auth-submit-btn">
          Daftar
        </button>
      </form>

      <div class="auth-switch-link">
        Sudah punya akun? <a href="index.php">Masuk di sini</a>
      </div>
    </section>
  </main>

  <footer class="auth-foot">
    <span>&copy; 2024 D'Tools &middot; Sistem Informasi Peminjaman Alat</span>
  </footer>

  <script>
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
      btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.togglePassword);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        this.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
      });
    });
  </script>
</body>

</html>

<?php $conn->close(); ?>
