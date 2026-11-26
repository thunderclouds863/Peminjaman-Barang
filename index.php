<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";

$conn = new mysqli($servername, $username, $password, $dbname);
session_start();

// Error message variable
$error_message = '';

if (isset($_POST['submit'])) {
  $role = $_POST['role'];
  $username = $_POST['username'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE username = '$username' AND role = '$role'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($password === $user['password']) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      if ($user['role'] == 'Peminjam Alat') {
        header("Location: home.php");
      }
      if ($role == 'Divisi Alat') {
        header("Location: divisi_dashboard.php");
      }
      if ($role == 'Bendahara') {
        header("Location: bendahara_dashboard.php");
      } else {
        $error_message = "Invalid credentials";
      }
    } else {
      $error_message = "Invalid credentials";
    }
  } else {
    $error_message = "Invalid credentials";
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
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css">
</head>

<body class="auth-body">
  <main class="auth-card">
    <div class="auth-brand">
      <img src="logo.png" alt="Logo D'Tools">
      <h1>Sistem Informasi Peminjaman Alat</h1>
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

    <form method="POST">
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
          <input type="text" id="username" name="username" class="form-control-dt" placeholder="Username" autocomplete="username" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label-dt" for="password">Password</label>
        <div class="input-icon-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" id="password" name="password" class="form-control-dt" placeholder="Password" autocomplete="current-password" required>
        </div>
      </div>

      <button type="submit" name="submit" value="login" class="btn-brand auth-submit-btn">
        Login
      </button>
    </form>

    <div class="auth-switch-link" id="link-section">
      Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>
  </main>

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
  </script>
</body>

</html>

<?php $conn->close(); ?>
