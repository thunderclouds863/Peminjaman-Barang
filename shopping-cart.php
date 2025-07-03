<?php
include('styles.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Peminjam Alat') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";  // Updated database name
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize the cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_GET['add_to_cart'])) {
    $item_id = $_GET['add_to_cart'];
    if ($item_id > 0) {
        if (!isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id] = ['quantity' => 0];
        }
        $_SESSION['cart'][$item_id]['quantity']++;
    }
}

// Handle remove from cart
if (isset($_GET['remove_from_cart'])) {
    $item_id = $_GET['remove_from_cart'];
    if (isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
    }
}

// Handle quantity change
if (isset($_POST['update_quantity'])) {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $item_id => $quantity) {
            if (isset($_SESSION['cart'][$item_id])) {
                $quantity = max(1, min(10, intval($quantity))); // Ensure quantity is between 1 and 10
                $_SESSION['cart'][$item_id]['quantity'] = $quantity;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>/* General Styles */
html {
    height: 100%;
    margin-bottom: 90px;
    padding: 0;
    overflow-y: auto;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(45deg, #a0d9d2, #72c3b1, #63a6b1, #e1e8ee);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    padding: 0;
    align-items: center;
    margin-bottom: 80px;
    margin-top:80px;
}

.content {
    flex-grow: 1;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    max-width: 800px;
    width: 90%;
}

h2 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 2px solid #e0e0e0;
    transition: background-color 0.3s;
    position: relative;
}

.cart-item:hover {
    background-color: #f9f9f9;
}

.cart-item-info {
    flex: 1;
}

.cart-item-actions {
    display: flex;
    align-items: center;
}

.cart-item-actions input {
    width: 50px;
    text-align: center;
    padding: 8px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin: 0 10px;
    transition: all 0.3s ease;
}

.cart-item-actions input:focus {
    border-color: #ff7043;
    outline: none;
}

.cart-item-actions button {
    background-color: #72c3b1;
    color: white;
    border: none;
    padding: 10px 15px;
    margin: 0 5px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
    transition: background-color 0.3s ease;y
}

.cart-item-actions button:hover {
    background-color:  #e1e8ee;
}

.remove-button {
    background-color: #72c3b1;
    padding:8px;
    color: white;
    text-decoration: none;
    font-size: 14px;
    margin-left: 10px;
    transition: color 0.3s ease;
}

.remove-button:hover {
    background-color:  #8fd9a8;
    color: white;
    text-decoration: underline;
}

.checkout-button {
    display: block;
    width: 100%;
    background: linear-gradient(135deg, #8fd9a8, #70c5b0);
    color: white;
    padding: 15px;
    border-radius: 5px;
    text-align: center;
    font-size: 18px;
    text-decoration: none;
    margin-top: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease;
}

.checkout-button:hover {
    background: linear-gradient(45deg, #a0d9d2, #72c3b1, #63a6b1, #e1e8ee);
}

footer {
    background-color: #5a9e97; /* Updated to match the header color scheme */
    color: white;
    text-align: center;
    padding: 15px;
    position: fixed;
    bottom: 0;
    width: 100%;
    font-size: 14px;
}
</style>
</head>

<body>

    <div class="content">
        <h2>Cart Peminjaman</h2>

        <form method="POST">
            <?php
            if (empty($_SESSION['cart'])) {
                echo "<p>Your cart is empty.</p>";
            } else {
                foreach ($_SESSION['cart'] as $item_id => $item) {
                    if ($item_id <= 0) {
                        unset($_SESSION['cart'][$item_id]);
                        continue;
                    }

                    if (is_array($item) && isset($item['quantity'])) {
                        $sql = "SELECT * FROM item WHERE id = $item_id";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            $product = $result->fetch_assoc();
                            $total_price = $product['price'] * $item['quantity'];
                            ?>

                            <div class="cart-item" id="cart-item-<?php echo $item_id; ?>">
                                <div class="cart-item-info">
                                    <h3><?php echo $product['name']; ?></h3>
                                    <p class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?> x
                                        <?php echo $item['quantity']; ?>
                                    </p>
                                </div>

                                <div class="cart-item-actions">
                                    <!-- Decrease button -->
                                    <button type="button" class="quantity-button decrease" data-item-id="<?php echo $item_id; ?>">-</button>
                                    <input type="number" name="quantity[<?php echo $item_id; ?>]"
                                           value="<?php echo $item['quantity']; ?>" min="1" max="10" class="quantity-input">
                                    <!-- Increase button -->
                                    <button type="button" class="quantity-button increase" data-item-id="<?php echo $item_id; ?>">+</button>

                                    <!-- Remove button -->
                                    <a href="shopping-cart.php?remove_from_cart=<?php echo $item_id; ?>"
                                       class="remove-button"
                                       onclick="return confirm('Are you sure you want to remove this item?');">
                                       Remove
                                    </a>
                                </div>
                            </div>

                            <?php
                        } else {
                            echo "<p>Item not found for ID: $item_id</p>";
                        }
                    } else {
                        echo "<p>Error: Cart item is not structured properly for item ID: $item_id</p>";
                    }
                }
            }
            ?>
            <a href="checkout.php" class="checkout-button">Pinjam Sekarang</a>
        </form>
    </div>


    <script>// Update quantity dynamically using JavaScript
const decreaseButtons = document.querySelectorAll('.decrease');
const increaseButtons = document.querySelectorAll('.increase');

decreaseButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent any default behavior
        const itemId = this.getAttribute('data-item-id');
        let input = document.querySelector(`input[name="quantity[${itemId}]"]`);
        let currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 2;
            updateCart(itemId, currentValue - 2);
        }
    });
});

increaseButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent any default behavior
        const itemId = this.getAttribute('data-item-id');
        let input = document.querySelector(`input[name="quantity[${itemId}]"]`);
        let currentValue = parseInt(input.value);
        if (currentValue < 10) {
            input.value = currentValue;
            updateCart(itemId, currentValue);
        }
    });
});

// Function to update the cart
function updateCart(itemId, newQuantity) {
    // Create form data
    const formData = new FormData();
    formData.append('update_quantity', true);
    formData.append(`quantity[${itemId}]`, newQuantity);

    // Send AJAX request
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        // Optionally refresh the page or update the UI
        window.location.reload();
    })
    .catch(error => console.error('Error:', error));
}</script>

</body>

</html>

<?php
$conn->close();
?>
