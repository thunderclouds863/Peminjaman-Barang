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
        if ($password===$user['password']) {
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['role'] = $user['role'];
          if ($user['role']== 'Peminjam Alat'){
          header("Location: home.php");
          }if ($role== 'Divisi Alat'){
            header("Location: divisi_dashboard.php");
          }if ($role== 'Bendahara'){
            header("Location: bendahara_dashboard.php");
        } else {
          $error_message = "Invalid credentials";
        }
      } else {
        $error_message = "Invalid credentials";
      }
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SISTEM INFORMASI PEMINJAMAN ALAT</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fef5e7;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 400px;
      padding: 20px;
      background: #fff5e5;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border-radius: 15px;
      text-align: center;
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    h2 {
      color: #ff8a65;
      margin-bottom: 20px;
      text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3); /* Adding shadow to the title */
    }

    form {
      display: flex;
      flex-direction: column;
    }

    input,
    select,
    button {
      margin: 10px 0;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ffccbc;
      border-radius: 8px;
      background-color: #fff;
      box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1); /* Adding shadow to the input boxes */
      transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    input:focus,
    select:focus,
    button:focus {
      outline: none;
      box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2); /* Enhanced shadow on focus */
      transform: scale(1.05);
    }

    button {
      background-color: #ff8a65;
      color: white;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #ff7043;
    }

    .link {
      margin-top: 10px;
      font-size: 14px;
    }

    .link a {
      color: #ff8a65;
      text-decoration: none;
      font-weight: bold;
    }

    .link a:hover {
      text-decoration: underline;
    }

    .error-popup {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #ff5252;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      animation: slideIn 0.5s ease-out, fadeOut 4s ease-in forwards;
    }

    @keyframes slideIn {
      from {
        transform: translateX(-50%) translateY(-20px);
        opacity: 0;
      }

      to {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
      }
    }

    @keyframes fadeOut {
      90% {
        opacity: 1;
      }

      100% {
        opacity: 0;
      }
    }

    img {
      width: 100px;
      margin-bottom: 20px;
      animation: fadeInLogo 2s ease-in-out;
    }

    @keyframes fadeInLogo {
      0% {
        opacity: 0;
        transform: scale(0.5);
      }

      100% {
        opacity: 1;
        transform: scale(1);
      }
    }
  </style>
</head>

<body>
  <?php if (!empty($error_message)): ?>
    <div class="error-popup"><?php echo $error_message; ?></div>
  <?php endif; ?>

  <div class="container">
    <!-- Logo Image -->
    <img src="logo.png" alt="Logo">

    <h2>SISTEM INFORMASI PEMINJAMAN ALAT</h2>
    <form method="POST">
      <select name="role" id="role" required>
        <option value="" disabled selected>-- Select Role --</option>
        <option value="Peminjam Alat">Peminjam Alat</option>
        <option value="Bendahara">Bendahara</option>
        <option value="Divisi Alat">Divisi Alat</option>
      </select>
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="submit" value="login">Login</button>
    </form>
    <div class="link" id="link-section">
      <p>Don't have an account? <a href="register.php">Register Here</a></p>
    </div>
  </div>

  <script>
    const roleSelect = document.getElementById('role');
    const linkSection = document.getElementById('link-section');

    roleSelect.addEventListener('change', function () {
      if (this.value === 'Divisi Alat' || this.value === 'Divisi Alat') {
        linkSection.style.display = 'none';
      } else {
        linkSection.style.display = 'block';
      }
    });
  </script>
</body>

</html>

<?php $conn->close(); ?>
