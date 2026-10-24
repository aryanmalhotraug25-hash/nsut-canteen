<?php
// 1. Connect to the database
include 'db_connect.php';

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']); // Getting the hidden user_id (1)
    $items_ordered = $_POST['items'];     // This is an array of item_id => quantity

    $total_price = 0;
    $valid_items = []; // To store items where quantity > 0

    // 2. Loop through submitted items to calculate the total price securely
    foreach ($items_ordered as $item_id => $quantity) {
        $quantity = intval($quantity);
        
        if ($quantity > 0) {
            // Fetch the current price from the database to prevent frontend manipulation
            $price_query = "SELECT price FROM Menu_Items WHERE item_id = " . intval($item_id);
            $result = $conn->query($price_query);
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $total_price += ($row['price'] * $quantity);
                
                // Save valid items for the next step
                $valid_items[$item_id] = $quantity;
            }
        }
    }

    // 3. Insert the data into the database if there are valid items
    if ($total_price > 0) {
        
        // Step A: Create the main order record
        $insert_order_sql = "INSERT INTO Orders (user_id, total_price, order_status) 
                             VALUES ($user_id, $total_price, 'Pending')";
        
        if ($conn->query($insert_order_sql) === TRUE) {
            // Get the auto-generated order_id from the insert we just did
            $new_order_id = $conn->insert_id;

            // Step B: Loop through valid items and insert them into Order_Details
            foreach ($valid_items as $item_id => $quantity) {
                $insert_detail_sql = "INSERT INTO Order_Details (order_id, item_id, quantity) 
                                      VALUES ($new_order_id, $item_id, $quantity)";
                $conn->query($insert_detail_sql);
            }

            // Success message
            echo "<div style='font-family: sans-serif; padding: 20px;'>";
            echo "<h2 style='color: green;'>Order Placed Successfully!</h2>";
            echo "<p>Your Order ID is: <strong>#" . $new_order_id . "</strong></p>";
            echo "<p>Total Amount: <strong>₹" . number_format($total_price, 2) . "</strong></p>";
            echo "<p>Status: <strong>Pending</strong></p>";
            echo "<br><a href='index.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Menu</a>";
            echo "</div>";

        } else {
            echo "Error creating order: " . $conn->error;
        }
    } else {
        echo "<h2 style='font-family: sans-serif;'>No items selected.</h2>";
        echo "<a href='index.php'>Go back and select quantities</a>";
    }
}

$conn->close();
?>