<?php
session_start();
require_once __DIR__ . '/includes/auth_recovery.php';

$conn = dt_db_connect();
$error_message = '';

if (isset($_POST['submit'])) {
    $role = trim($_POST['role'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (dt_verify_password($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'Peminjam Alat') {
                header("Location: home.php");
                exit();
            } elseif ($user['role'] === 'Divisi Alat') {
                header("Location: divisi_dashboard.php");
                exit();
            } elseif ($user['role'] === 'Bendahara') {
                header("Location: bendahara_dashboard.php");
                exit();
            } else {
                $error_message = "Kredensial tidak valid.";
            }
        } else {
            $error_message = "Username, password, atau role tidak sesuai.";
        }
    } else {
        $error_message = "Username, password, atau role tidak sesuai.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — D'Tools</title>
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
    <span class="auth-topbar-tag">Portal Laboratorium</span>
  </header>

  <main class="auth-main">
    <section class="auth-panel" aria-labelledby="loginHeading">
      <div class="auth-panel-head">
        <div class="auth-logo-mark">
          <img src="logo.png" alt="Logo D'Tools">
        </div>
        <h1 id="loginHeading">Sistem Informasi Peminjaman Alat</h1>
        <p class="auth-panel-sub">Masuk menggunakan akun dan peran Anda.</p>
      </div>

      <?php if (!empty($error_message)): ?>
        <div class="alert-banner error" role="alert">
          <i class="fas fa-circle-exclamation"></i>
          <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($_GET['message'])): ?>
        <div class="alert-banner success" role="status">
          <i class="fas fa-circle-check"></i>
          <span><?php echo htmlspecialchars($_GET['message']); ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" id="loginForm">
        <div class="form-group">
          <label class="form-label-dt" for="role">Role</label>
          <div class="input-icon-wrap">
            <i class="fas fa-user-shield"></i>
            <select name="role" id="role" class="form-control-dt" required>
              <option value="" disabled selected>-- Pilih Role --</option>
              <option value="Peminjam Alat">Peminjam Alat</option>
              <option value="Bendahara">Bendahara</option>
              <option value="Divisi Alat">Divisi Alat</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label-dt" for="username">Username</label>
          <div class="input-icon-wrap">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" class="form-control-dt" placeholder="Masukkan username" autocomplete="username" required>
          </div>
        </div>

        <div class="form-group">
          <div class="label-row">
            <label class="form-label-dt" for="password">Password</label>
            <a href="forgot-password.php" class="forgot-link">Lupa password?</a>
          </div>
          <div class="input-icon-wrap has-toggle">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" class="form-control-dt" placeholder="Masukkan password" autocomplete="current-password" required>
            <button type="button" class="password-toggle-btn" data-toggle-password="password" aria-label="Tampilkan password">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" name="submit" value="login" class="btn-brand auth-submit-btn">
          Masuk
        </button>
      </form>

      <div class="auth-switch-link" id="link-section">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
      </div>
    </section>
  </main>

  <footer class="auth-foot">
    <span>&copy; 2024 D'Tools &middot; Sistem Informasi Peminjaman Alat</span>
  </footer>

  <script>
    const roleSelect = document.getElementById('role');
    const linkSection = document.getElementById('link-section');

    roleSelect.addEventListener('change', function () {
      if (this.value === 'Divisi Alat' || this.value === 'Bendahara') {
        linkSection.style.display = 'none';
      } else {
        linkSection.style.display = 'block';
      }
    });

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
