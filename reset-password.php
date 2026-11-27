<?php
session_start();
require_once __DIR__ . '/includes/auth_recovery.php';

$conn = dt_db_connect();
dt_ensure_recovery_schema($conn);

$rawToken = trim($_GET['token'] ?? $_POST['token'] ?? '');
$tokenCheck = dt_validate_reset_token($conn, $rawToken);
$tokenState = $tokenCheck['status']; // valid | invalid | expired | used

$error_message = '';
$reset_completed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenState === 'valid') {
    $csrf = $_POST['csrf_token'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!dt_verify_csrf($csrf)) {
        $error_message = 'Sesi formulir tidak valid. Silakan muat ulang halaman.';
    } elseif (!dt_check_rate_limit('reset_password', 8, 900)) {
        $error_message = 'Terlalu banyak percobaan. Silakan coba lagi beberapa menit lagi.';
    } elseif (strlen($newPassword) < 8) {
        $error_message = 'Password baru minimal terdiri dari 8 karakter.';
    } elseif ($newPassword !== $confirmPassword) {
        $error_message = 'Konfirmasi password tidak cocok.';
    } else {
        $rec = $tokenCheck['record'];
        if (dt_complete_password_reset($conn, (int)$rec['reset_id'], (int)$rec['user_id'], $newPassword)) {
            session_regenerate_id(true);
            $reset_completed = true;
            $tokenState = 'completed';
        } else {
            $error_message = 'Gagal memperbarui password. Silakan coba lagi.';
        }
    }
}

$csrf_token = dt_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Password Baru — D'Tools</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css">
</head>

<body class="auth-stage">
  <header class="auth-topbar">
    <a href="index.php" class="auth-topbar-brand">
      <img src="logo.png" alt="D'Tools">
      <span>D'Tools</span>
    </a>
    <span class="auth-topbar-tag">Pengaturan Password</span>
  </header>

  <main class="auth-main">
    <section class="auth-panel" aria-labelledby="resetHeading">
      <?php if ($reset_completed): ?>
        <div class="auth-panel-head">
          <div class="auth-panel-icon success" aria-hidden="true">
            <i class="fas fa-check"></i>
          </div>
          <h1 id="resetHeading">Password Diperbarui</h1>
          <p class="auth-panel-sub">Password akun Anda berhasil diubah. Gunakan password baru untuk masuk.</p>
        </div>

        <a href="index.php" class="btn-brand auth-submit-btn">
          Masuk ke D'Tools
        </a>

      <?php elseif ($tokenState !== 'valid'): ?>
        <div class="auth-panel-head">
          <div class="auth-panel-icon danger" aria-hidden="true">
            <i class="fas fa-link-slash"></i>
          </div>
          <h1 id="resetHeading">
            <?php echo $tokenState === 'expired' ? 'Tautan Kedaluwarsa' : 'Tautan Tidak Valid'; ?>
          </h1>
          <p class="auth-panel-sub">
            <?php
            if ($tokenState === 'expired') {
                echo 'Tautan reset password telah kedaluwarsa. Ajukan permintaan baru untuk melanjutkan.';
            } elseif ($tokenState === 'used') {
                echo 'Tautan reset password ini sudah pernah digunakan.';
            } else {
                echo 'Tautan reset password tidak valid atau tidak lengkap.';
            }
            ?>
          </p>
        </div>

        <a href="forgot-password.php" class="btn-brand auth-submit-btn">
          Minta Tautan Baru
        </a>

        <div class="auth-switch-link">
          <a href="index.php"><i class="fas fa-arrow-left" style="margin-right: 6px; font-size: 11.5px;"></i>Kembali ke Login</a>
        </div>

      <?php else: ?>
        <div class="auth-panel-head">
          <div class="auth-panel-icon" aria-hidden="true">
            <i class="fas fa-shield-halved"></i>
          </div>
          <h1 id="resetHeading">Buat Password Baru</h1>
          <p class="auth-panel-sub">Masukkan password baru minimal 8 karakter untuk akun Anda.</p>
        </div>

        <?php if ($error_message !== ''): ?>
          <div class="alert-banner error" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <span><?php echo htmlspecialchars($error_message); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" id="resetForm" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($rawToken); ?>">

          <div class="form-group">
            <label class="form-label-dt" for="password">Password Baru</label>
            <div class="input-icon-wrap has-toggle">
              <i class="fas fa-lock"></i>
              <input
                type="password"
                id="password"
                name="password"
                class="form-control-dt"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                minlength="8"
                required
              >
              <button type="button" class="password-toggle-btn" data-toggle-password="password" aria-label="Tampilkan password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <p class="field-error" id="passwordError" role="alert" hidden></p>
          </div>

          <div class="form-group">
            <label class="form-label-dt" for="confirm_password">Konfirmasi Password</label>
            <div class="input-icon-wrap has-toggle">
              <i class="fas fa-lock"></i>
              <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                class="form-control-dt"
                placeholder="Ulangi password baru"
                autocomplete="new-password"
                minlength="8"
                required
              >
              <button type="button" class="password-toggle-btn" data-toggle-password="confirm_password" aria-label="Tampilkan konfirmasi password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <p class="field-error" id="confirmError" role="alert" hidden></p>
          </div>

          <button type="submit" class="btn-brand auth-submit-btn" id="resetSubmitBtn">
            <span class="btn-label">Simpan Password Baru</span>
            <span class="btn-spinner" aria-hidden="true" hidden><i class="fas fa-circle-notch fa-spin"></i></span>
          </button>
        </form>

        <div class="auth-switch-link">
          <a href="index.php"><i class="fas fa-arrow-left" style="margin-right: 6px; font-size: 11.5px;"></i>Kembali ke Login</a>
        </div>
      <?php endif; ?>
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

    (function () {
      const form = document.getElementById('resetForm');
      if (!form) return;
      const pwd = document.getElementById('password');
      const confirmPwd = document.getElementById('confirm_password');
      const pwdErr = document.getElementById('passwordError');
      const confirmErr = document.getElementById('confirmError');
      const submitBtn = document.getElementById('resetSubmitBtn');

      function validate() {
        let ok = true;
        if (pwd.value.length < 8) {
          pwdErr.textContent = 'Password minimal 8 karakter.';
          pwdErr.hidden = false;
          pwd.classList.add('is-invalid');
          ok = false;
        } else {
          pwdErr.hidden = true;
          pwd.classList.remove('is-invalid');
        }

        if (confirmPwd.value !== pwd.value) {
          confirmErr.textContent = 'Konfirmasi password tidak cocok.';
          confirmErr.hidden = false;
          confirmPwd.classList.add('is-invalid');
          ok = false;
        } else {
          confirmErr.hidden = true;
          confirmPwd.classList.remove('is-invalid');
        }
        return ok;
      }

      pwd.addEventListener('input', () => { if (!pwdErr.hidden || !confirmErr.hidden) validate(); });
      confirmPwd.addEventListener('input', () => { if (!confirmErr.hidden) validate(); });

      form.addEventListener('submit', function (e) {
        if (!validate()) {
          e.preventDefault();
          return;
        }
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-label').textContent = 'Menyimpan...';
        submitBtn.querySelector('.btn-spinner').hidden = false;
      });
    })();
  </script>
</body>

</html>
<?php $conn->close(); ?>
