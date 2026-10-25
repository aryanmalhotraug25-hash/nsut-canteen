<?php
require_once 'auth.php';
include 'db_connect.php';

// Must be logged in; the user ID now comes from the session
$user_id = require_login();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Redirect back to menu if someone tries to access cart.php directly
    header("Location: index.php");
    exit();
}

$items_ordered = $_POST['items'] ?? [];

$total_price = 0;
$cart_items = [];

// Fetch prices and calculate totals
foreach ($items_ordered as $item_id => $quantity) {
    $quantity = intval($quantity);
    if ($quantity > 0) {
        $price_query = "SELECT item_name, price FROM Menu_Items WHERE item_id = " . intval($item_id);
        $result = $conn->query($price_query);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $subtotal = $row['price'] * $quantity;
            $total_price += $subtotal;
            
            // Store item details for displaying in the cart
            $cart_items[] = [
                'id' => $item_id,
                'name' => $row['item_name'],
                'price' => $row['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Review & Payment</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; }
        .summary-box { border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: #f9f9f9; }
        .cart-item { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding: 10px 0; }
        .total-row { display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; margin-top: 20px; color: green; }
        .payment-btn { padding: 12px 20px; margin-top: 15px; width: 100%; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; color: white; }
        .upi-btn { background-color: #6f42c1; }
        .upi-btn:hover { background-color: #59339d; }
        .card-btn { background-color: #17a2b8; margin-top: 10px; }
        .card-btn:hover { background-color: #138496; }
        .back-link { display: block; margin-top: 15px; text-align: center; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

    <h2>Review Your Order</h2>

    <?php if ($total_price > 0): ?>
        <div class="summary-box">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                    <span>₹<?php echo number_format($item['subtotal'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="total-row">
                <span>Total Amount:</span>
                <span>₹<?php echo number_format($total_price, 2); ?></span>
            </div>

            <!-- This form forwards the exact same structure to place_order.php -->
            <form action="place_order.php" method="POST" style="margin-top: 20px;">
                <?php foreach ($cart_items as $item): ?>
                    <input type="hidden" name="items[<?php echo (int) $item['id']; ?>]" value="<?php echo $item['quantity']; ?>">
                <?php endforeach; ?>

                <p style="text-align: center; font-size: 14px; color: #666;">Choose a payment method to complete your order:</p>
                <button type="submit" class="payment-btn upi-btn">Pay via Dummy UPI</button>
                <button type="submit" class="payment-btn card-btn">Pay via Dummy Card</button>
            </form>
        </div>
    <?php else: ?>
        <div class="summary-box">
            <p>Your cart is empty.</p>
        </div>
    <?php endif; ?>

    <a href="index.php" class="back-link">← Back to Menu</a>

</body>
</html>