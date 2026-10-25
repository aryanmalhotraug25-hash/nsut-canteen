<?php
require_once 'auth.php';
// Staff only: students/faculty get 'Access denied', logged-out visitors go to login
require_role('staff');

// Connect to the database
include 'db_connect.php';

// If the "Mark as Delivered" button was clicked, update the database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_delivered'])) {
    $order_id_to_update = intval($_POST['order_id']);
    
    // Update the status to 'Delivered'
    $update_sql = "UPDATE Orders SET order_status = 'Delivered' WHERE order_id = $order_id_to_update";
    
    if ($conn->query($update_sql) === TRUE) {
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>Order #$order_id_to_update marked as delivered!</div>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Staff Dashboard</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; background-color: #f4f4f9; }
        .order-card { background: white; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .order-header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .order-header h3 { margin: 0; color: #333; }
        .items-list { margin-bottom: 15px; }
        .items-list ul { list-style-type: none; padding: 0; }
        .items-list li { padding: 5px 0; font-size: 16px; }
        .btn { padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #218838; }
    </style>
</head>
<body>

    <p style="text-align:right">Logged in as <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong> | <a href="logout.php">Log out</a></p>
    <h2>Staff Dashboard - Live Orders</h2>

    <?php
    // Fetch all orders that are still 'Pending' (this is a relational JOIN query)
    $orders_sql = "SELECT Orders.order_id, Users.name, Orders.total_price, Orders.created_at 
                   FROM Orders 
                   JOIN Users ON Orders.user_id = Users.user_id 
                   WHERE Orders.order_status = 'Pending' 
                   ORDER BY Orders.created_at ASC";
    
    $orders_result = $conn->query($orders_sql);

    if ($orders_result->num_rows > 0) {
        // Loop through each pending order
        while ($order = $orders_result->fetch_assoc()) {
            $current_order_id = $order['order_id'];
            ?>
            
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <h3>Order #<?php echo $current_order_id; ?></h3>
                        <p style="margin: 5px 0; color: #666;">
                            <strong>Student:</strong> <?php echo htmlspecialchars($order['name']); ?> <br>
                            <strong>Time:</strong> <?php echo $order['created_at']; ?>
                        </p>
                    </div>
                    <div>
                        <h3 style="color: #28a745;">₹<?php echo $order['total_price']; ?></h3>
                    </div>
                </div>
                
                <div class="items-list">
                    <strong>Items to Prepare:</strong>
                    <ul>
                        <?php
                        // Fetch the specific food items mapped to this current order ID
                        $items_sql = "SELECT Menu_Items.item_name, Order_Details.quantity 
                                      FROM Order_Details 
                                      JOIN Menu_Items ON Order_Details.item_id = Menu_Items.item_id 
                                      WHERE Order_Details.order_id = $current_order_id";
                        $items_result = $conn->query($items_sql);
                        
                        while ($item = $items_result->fetch_assoc()) {
                            // Displays like: "2x Samosa"
                            echo "<li><strong>" . $item['quantity'] . "x</strong> " . htmlspecialchars($item['item_name']) . "</li>";
                        }
                        ?>
                    </ul>
                </div>

                <!-- Form to update the status -->
                <form method="POST" action="">
                    <input type="hidden" name="order_id" value="<?php echo $current_order_id; ?>">
                    <button type="submit" name="mark_delivered" class="btn">Mark as Delivered (Clear)</button>
                </form>
            </div>
            
            <?php
        }
    } else {
        echo "<div style='background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;'>No pending orders right now. The queue is clear!</div>";
    }

    $conn->close();
    ?>

</body>
</html>