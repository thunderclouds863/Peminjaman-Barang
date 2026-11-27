<?php
session_start();
require_once __DIR__ . '/includes/auth_recovery.php';

$conn = dt_db_connect();
dt_ensure_recovery_schema($conn);

$status_type = '';
$status_message = '';
$infra_notice = '';
$dev_preview_url = '';
$submitted_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_email = trim($_POST['email'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!dt_verify_csrf($csrf)) {
        $status_type = 'error';
        $status_message = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } elseif (!dt_check_rate_limit('forgot_password', 5, 900)) {
        $status_type = 'error';
        $status_message = 'Terlalu banyak permintaan. Silakan tunggu beberapa menit sebelum mencoba lagi.';
    } elseif ($submitted_email === '' || !filter_var($submitted_email, FILTER_VALIDATE_EMAIL)) {
        $status_type = 'error';
        $status_message = 'Masukkan alamat email yang valid.';
    } else {
        // Always return a generic response to prevent account enumeration
        $status_type = 'success';
        $status_message = 'Jika email tersebut terdaftar di D\'Tools, tautan pengaturan ulang password telah diproses.';

        $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $submitted_email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            $reset = dt_create_password_reset($conn, (int)$user['id'], 30);
            if ($reset !== null) {
                $resetUrl = dt_build_reset_url($reset['token']);
                $delivery = dt_send_recovery_email($user['email'], $user['username'], $resetUrl, $reset['ttl_minutes']);

                if (!$delivery['configured']) {
                    $infra_notice = 'Pengiriman email (SMTP) belum dikonfigurasi pada server ini. Token reset tetap dibuat secara aman di database.';
                    if (getenv('ALLOW_DEV_RESET_PREVIEW') === '1') {
                        $dev_preview_url = $resetUrl;
                    }
                }
            }
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
  <title>Reset Password — D'Tools</title>
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
    <span class="auth-topbar-tag">Pemulihan Akun</span>
  </header>

  <main class="auth-main">
    <section class="auth-panel" aria-labelledby="forgotHeading">
      <div class="auth-panel-head">
        <div class="auth-panel-icon" aria-hidden="true">
          <i class="fas fa-key"></i>
        </div>
        <h1 id="forgotHeading">Reset Password</h1>
        <p class="auth-panel-sub">Masukkan alamat email terdaftar untuk menerima tautan pengaturan ulang password.</p>
      </div>

      <?php if ($status_message !== ''): ?>
        <div class="alert-banner <?php echo htmlspecialchars($status_type); ?>" role="<?php echo $status_type === 'error' ? 'alert' : 'status'; ?>">
          <i class="fas <?php echo $status_type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
          <span><?php echo htmlspecialchars($status_message); ?></span>
        </div>
      <?php endif; ?>

      <?php if ($infra_notice !== ''): ?>
        <div class="alert-banner info" role="note">
          <i class="fas fa-circle-info"></i>
          <span><?php echo htmlspecialchars($infra_notice); ?></span>
        </div>
      <?php endif; ?>

      <?php if ($dev_preview_url !== ''): ?>
        <div class="alert-banner info" role="note">
          <i class="fas fa-flask"></i>
          <span>Mode pengembangan: <a href="<?php echo htmlspecialchars($dev_preview_url); ?>">Buka tautan reset</a></span>
        </div>
      <?php endif; ?>

      <form method="POST" id="forgotForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
          <label class="form-label-dt" for="email">Email Terdaftar</label>
          <div class="input-icon-wrap">
            <i class="fas fa-envelope"></i>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control-dt"
              placeholder="nama@dtools.lab"
              autocomplete="email"
              value="<?php echo htmlspecialchars($submitted_email); ?>"
              required
            >
          </div>
          <p class="field-error" id="emailError" role="alert" hidden></p>
        </div>

        <button type="submit" class="btn-brand auth-submit-btn" id="forgotSubmitBtn">
          <span class="btn-label">Kirim Tautan Reset</span>
          <span class="btn-spinner" aria-hidden="true" hidden><i class="fas fa-circle-notch fa-spin"></i></span>
        </button>
      </form>

      <div class="auth-switch-link">
        <a href="index.php"><i class="fas fa-arrow-left" style="margin-right: 6px; font-size: 11.5px;"></i>Kembali ke Login</a>
      </div>
    </section>
  </main>

  <footer class="auth-foot">
    <span>&copy; 2024 D'Tools &middot; Sistem Informasi Peminjaman Alat</span>
  </footer>

  <script>
    (function () {
      const form = document.getElementById('forgotForm');
      const emailInput = document.getElementById('email');
      const emailError = document.getElementById('emailError');
      const submitBtn = document.getElementById('forgotSubmitBtn');

      function validateEmail() {
        const val = emailInput.value.trim();
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
        if (!val) {
          emailError.textContent = 'Alamat email wajib diisi.';
          emailError.hidden = false;
          emailInput.classList.add('is-invalid');
          return false;
        }
        if (!valid) {
          emailError.textContent = 'Format alamat email tidak valid.';
          emailError.hidden = false;
          emailInput.classList.add('is-invalid');
          return false;
        }
        emailError.hidden = true;
        emailInput.classList.remove('is-invalid');
        return true;
      }

      emailInput.addEventListener('input', () => {
        if (!emailError.hidden) validateEmail();
      });

      form.addEventListener('submit', function (e) {
        if (!validateEmail()) {
          e.preventDefault();
          emailInput.focus();
          return;
        }
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-label').textContent = 'Memproses...';
        submitBtn.querySelector('.btn-spinner').hidden = false;
      });
    })();
  </script>
</body>

</html>
<?php $conn->close(); ?>
