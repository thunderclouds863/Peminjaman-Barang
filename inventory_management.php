<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Divisi Alat') {
  header("Location: index.php");
  exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_item') {
      $name = $_POST['name'];
      $description = $_POST['description'];
      $price = $_POST['price'];
      $stock = $_POST['stock'];

      $sql = "INSERT INTO item (name, description, price, stock) VALUES (?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssds", $name, $description, $price, $stock);
      $stmt->execute();
    }

    if ($action === 'edit_item') {
      $id = $_POST['id'];
      $name = $_POST['name'];
      $description = $_POST['description'];
      $price = $_POST['price'];
      $stock = $_POST['stock'];

      $sql = "UPDATE item SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssdsi", $name, $description, $price, $stock, $id);
      $stmt->execute();
    }

    if ($action === 'delete_item') {
      $id = $_POST['id'];
      $sql = "DELETE FROM item WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
      $stmt->execute();
    }
  }
}

// Fetch items
$items = $conn->query("SELECT * FROM item");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
  <style>
      html {
    height: 100%;
    margin-bottom: 30%;
    padding: 0;
    overflow-y: auto;
}
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
    footer {
      background-color: rgba(0, 0, 0, 0.7);
      color: white;
      text-align: center;
      padding: 10px;
      position: fixed;
      bottom: 0;
      width: 100%;
    }

    @keyframes gradient {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .container {
      padding: 20px;
      text-align: center;
    }

    h1 {
      color: #f2a6b7;
      margin-bottom: 20px;
      text-shadow: 3px 3px 8px rgba(255, 255, 255, 0.6);
    }

    button {
      background: linear-gradient(to right, #a8e063, #56ab2f);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    button:hover {
      background: linear-gradient(to right, #56ab2f, #a8e063);
      transform: translateY(-2px);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
    }

    button:active {
      transform: translateY(2px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    button.delete, button.close {
      background: linear-gradient(to right, #f46b45, #eea849);
      border: 2px solid #eea849;
    }

    table {
      width: 100%;
      margin: 20px 0;
      border-collapse: collapse;
      border: 2px solid #4db6ac; /* Teal border */
    }

    th, td {
      text-align: left;
      padding: 10px;
      border: 1px solid #4db6ac; /* Teal border for cells */
    }

    th {
      background-color: #f0f8ff;
      text-align: center;
      color: #004d40;
    }

    .popup {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup.active {
      display: flex;
    }

    .popup-content {
      background: linear-gradient(135deg, #e0f7fa, #ffccbc);
      padding: 30px;
      border-radius: 15px;
      text-align: center;
      width: 400px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .popup-content h3 {
      margin-bottom: 20px;
      color: #3b5998;
    }

    .popup-content label {
      display: block;
      margin: 10px 0;
      font-weight: bold;
      color: #3b5998;
    }

    .popup-content input,
    .popup-content textarea {
      width: 90%;
      padding: 10px;
      margin-bottom: 15px;
      border: 2px solid #3b5998;
      border-radius: 8px;
      font-size: 14px;
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
  <main class="container">
    <h1>Inventory Management</h1>
    <button onclick="togglePopup('add')">Add Item</button>

    <div class="popup" id="popup-add">
      <div class="popup-content">
        <h3>Add Item</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add_item">
          <label>Name: <input type="text" name="name" required></label>
          <label>Description: <textarea name="description" required></textarea></label>
          <label>Price: <input type="number" name="price" step="0.01" required></label>
          <label>Stock: <input type="number" name="stock" required></label>
          <button type="submit">Save Item</button>
          <button type="button" class="close" onclick="togglePopup('add')">Close</button>
        </form>
      </div>
    </div>

    <div class="popup" id="popup-edit">
      <div class="popup-content">
        <h3>Edit Item</h3>
        <form method="POST">
          <input type="hidden" name="action" value="edit_item">
          <input type="hidden" name="id" id="edit-id">
          <label>Name: <input type="text" name="name" id="edit-name" required></label>
          <label>Description: <textarea name="description" id="edit-description" required></textarea></label>
          <label>Price: <input type="number" name="price" id="edit-price" step="0.01" required></label>
          <label>Stock: <input type="number" name="stock" id="edit-stock" required></label>
          <button type="submit">Save Changes</button>
          <button type="button" class="close" onclick="togglePopup('edit')">Close</button>
        </form>
      </div>
    </div>

    <table id="itemTable" class="display">
      <thead>
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $items->fetch_assoc()) : ?>
          <tr>
            <td><?= $row['name'] ?></td>
            <td><?= $row['description'] ?></td>
            <td><?= number_format($row['price'], 2) ?></td>
            <td><?= $row['stock'] ?></td>
            <td>
              <button onclick="editItem(<?= $row['id'] ?>, '<?= $row['name'] ?>', '<?= $row['description'] ?>', <?= $row['price'] ?>, <?= $row['stock'] ?>)">Edit</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="delete_item">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="delete">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </main>
  <footer>
    <p>&copy; 2024 Sistem Peminjaman Alat. All Rights Reserved.</p>
</footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#itemTable').DataTable();
    });

    function togglePopup(popupType) {
      const popup = document.getElementById(`popup-${popupType}`);
      popup.classList.toggle('active');
    }

    function editItem(id, name, description, price, stock) {
      document.getElementById('edit-id').value = id;
      document.getElementById('edit-name').value = name;
      document.getElementById('edit-description').value = description;
      document.getElementById('edit-price').value = price;
      document.getElementById('edit-stock').value = stock;
      togglePopup('edit');
    }
  </script>
</body>

</html>

<?php
$conn->close();
?>
