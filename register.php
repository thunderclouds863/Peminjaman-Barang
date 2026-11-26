<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $sql_check = "SELECT * FROM users WHERE username = '$username'";
    $result_check = $conn->query($sql_check);
    if ($result_check->num_rows > 0) {
        header("Location: register.php?message=Username already exists!&type=error");
        exit();
    }

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
  <title>Registrasi — D'Tools</title>
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
      <h1>Registrasi Akun</h1>
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
          <input type="text" id="username" name="username" class="form-control-dt" placeholder="Username" autocomplete="username" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label-dt" for="password">Password</label>
        <div class="input-icon-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" id="password" name="password" class="form-control-dt" placeholder="Password" autocomplete="new-password" required>
        </div>
      </div>

      <button type="submit" name="register" value="register" class="btn-brand auth-submit-btn">
        Daftar
      </button>
    </form>

    <div class="auth-switch-link">
      Sudah punya akun? <a href="index.php">Masuk di sini</a>
    </div>
  </main>
</body>

</html>

<?php $conn->close(); ?>
