<?php
// Include the database connection you just created
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSUT Canteen Pre-Order</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; }
        .menu-item { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .menu-item h3 { margin: 0 0 10px 0; }
        .price { color: green; font-weight: bold; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <h2>Canteen Menu</h2>
    
    <!-- The form sends data to place_order.php using the POST method -->
    <form action="place_order.php" method="POST">
        
        <!-- Hardcoding user_id to 1 for testing purposes until you build a login system -->
        <input type="hidden" name="user_id" value="1">

        <?php
        // Fetch only available items from the database
        $sql = "SELECT item_id, item_name, price FROM Menu_Items WHERE is_available = 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Loop through each row in the result set
            while($row = $result->fetch_assoc()) {
                ?>
                <div class="menu-item">
                    <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                    <p class="price">₹<?php echo htmlspecialchars($row['price']); ?></p>
                    
                    <label>Quantity:</label>
                    <!-- Uses an array format items[item_id] to easily process multiple items later -->
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