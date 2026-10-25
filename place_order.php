<?php
require_once 'auth.php';
include 'db_connect.php';

// The logged-in user's ID comes from the session (replaces the old hidden user_id = 1)
$user_id = require_login();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit();
}

$items_ordered = $_POST['items'] ?? [];

$total_price = 0;
$valid_items = []; // item_id => quantity, only where quantity > 0

// Calculate the total from DATABASE prices (never trust prices from the browser)
$price_stmt = $conn->prepare("SELECT price FROM menu_items WHERE item_id = ? AND is_available = 1");
foreach ($items_ordered as $item_id => $quantity) {
    $item_id  = intval($item_id);
    $quantity = min(intval($quantity), 10);   // same max as the menu page

    if ($quantity > 0) {
        $price_stmt->bind_param('i', $item_id);
        $price_stmt->execute();
        $row = $price_stmt->get_result()->fetch_assoc();

        if ($row) {
            $total_price += ($row['price'] * $quantity);
            $valid_items[$item_id] = $quantity;
        }
    }
}
$price_stmt->close();

if ($total_price > 0) {
    // Order + its items are saved together: if anything fails, nothing is saved
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, order_status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param('id', $user_id, $total_price);
        $stmt->execute();
        $new_order_id = $conn->insert_id;
        $stmt->close();

        $detail = $conn->prepare("INSERT INTO order_details (order_id, item_id, quantity) VALUES (?, ?, ?)");
        foreach ($valid_items as $item_id => $quantity) {
            $detail->bind_param('iii', $new_order_id, $item_id, $quantity);
            $detail->execute();
        }
        $detail->close();

        $conn->commit();

        echo "<div style='font-family: sans-serif; padding: 20px;'>";
        echo "<h2 style='color: green;'>Order Placed Successfully!</h2>";
        echo "<p>Your Order ID is: <strong>#" . $new_order_id . "</strong></p>";
        echo "<p>Total Amount: <strong>₹" . number_format($total_price, 2) . "</strong></p>";
        echo "<p>Status: <strong>Pending</strong></p>";
        echo "<br><a href='my_orders.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Track My Orders</a> ";
        echo "<a href='index.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Menu</a>";
        echo "</div>";
    } catch (mysqli_sql_exception $ex) {
        $conn->rollback();
        echo "Error creating order. Please try again.";
    }
} else {
    echo "<h2 style='font-family: sans-serif;'>No items selected.</h2>";
    echo "<a href='index.php'>Go back and select quantities</a>";
}

$conn->close();
?>
