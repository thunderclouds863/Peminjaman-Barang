<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

// Check item stock
$sql = "SELECT stock FROM item WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
    if ($item['stock'] > 0) {
        // Reduce stock
        $conn->query("UPDATE item SET stock = stock - 1 WHERE id = $item_id");

        // Add to cart (session-based for simplicity)
        $_SESSION['cart'][$item_id] = ($_SESSION['cart'][$item_id] ?? 0) + 1;

        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item out of stock']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
}

$conn->close();
?>
