<?php
require_once 'auth.php';
include 'db_connect.php';

// Must be logged in. Canteen staff go to their dashboard instead of the student menu.
$user_id = require_login();
if (($_SESSION['role'] ?? '') === 'staff') {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSUT Canteen Pre-Order</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; }
        .nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd; }
        .nav a { color: #007bff; text-decoration: none; margin-left: 14px; }
        .menu-item { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .menu-item h3 { margin: 0 0 10px 0; }
        .price { color: green; font-weight: bold; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <div class="nav">
        <span>Hello, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
        <span>
            <a href="my_orders.php">My Orders</a>
            <a href="logout.php">Log out</a>
        </span>
    </div>

    <h2>Canteen Menu</h2>

    <!-- The form sends data to cart.php using the POST method -->
    <form action="cart.php" method="POST">

        <!-- user_id is no longer sent from the browser: cart.php and place_order.php read it from the session -->

        <?php
        // Fetch only available items from the database
        $sql = "SELECT item_id, item_name, price FROM Menu_Items WHERE is_available = 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                ?>
                <div class="menu-item">
                    <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                    <p class="price">₹<?php echo htmlspecialchars($row['price']); ?></p>

                    <label>Quantity:</label>
                    <input type="number" name="items[<?php echo $row['item_id']; ?>]" value="0" min="0" max="10">
                </div>
                <?php
            }
        } else {
            echo "<p>No items currently available.</p>";
        }
        $conn->close();
        ?>

        <button type="submit">Place Order</button>
    </form>

</body>
</html>
