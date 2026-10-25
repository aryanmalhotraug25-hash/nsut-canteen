<?php
require_once 'auth.php';
include 'db_connect.php';

$user_id = require_login();   // redirects to login.php if not logged in

// JOIN orders -> order_details -> menu_items, filtered ONLY by the logged-in user's ID.
// (order_details has no price column, so the item price comes from menu_items.)
$sql = "SELECT o.order_id, o.created_at, o.total_price, o.order_status,
               m.item_name, m.price, od.quantity
        FROM orders o
        JOIN order_details od ON od.order_id = o.order_id
        JOIN menu_items m     ON m.item_id   = od.item_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC, o.order_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Group the flat rows into one entry per order
$orders = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['order_id'];
    if (!isset($orders[$id])) {
        $orders[$id] = [
            'created_at'   => $row['created_at'],
            'total_price'  => $row['total_price'],
            'order_status' => $row['order_status'],
            'items'        => [],
        ];
    }
    $orders[$id]['items'][] = [
        'name'     => $row['item_name'],
        'quantity' => $row['quantity'],
        'price'    => $row['price'],
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="refresh" content="30"> <!-- reloads every 30s so the status stays live -->
  <title>My orders - NSUT Canteen</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <strong>NSUT Canteen</strong>
    <nav>
      <a href="index.php">Menu</a>
      <a href="my_orders.php">My orders</a>
      <a href="logout.php">Log out (<?= e($_SESSION['name'] ?? '') ?>)</a>
    </nav>
  </div>

  <div class="wrap">
    <h1>My orders</h1>

    <?php if (!$orders): ?>
      <div class="card empty">
        You haven't placed any orders yet. <a href="index.php">Browse the menu</a>.
      </div>
    <?php endif; ?>

    <?php foreach ($orders as $id => $o): ?>
      <?php $status_class = strtolower($o['order_status']); // pending / ready / delivered ?>
      <div class="card order">
        <div class="order-head">
          <div>
            <h2>Order #<?= (int) $id ?></h2>
            <div class="when"><?= e(date('d M Y, h:i A', strtotime($o['created_at']))) ?></div>
          </div>
          <span class="badge <?= e($status_class) ?>"><?= e($o['order_status']) ?></span>
        </div>

        <table>
          <thead>
            <tr><th>Item</th><th class="num">Qty</th><th class="num">Amount</th></tr>
          </thead>
          <tbody>
            <?php foreach ($o['items'] as $item): ?>
              <tr>
                <td><?= e($item['name']) ?></td>
                <td class="num"><?= (int) $item['quantity'] ?></td>
                <td class="num">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="total">Total: ₹<?= number_format($o['total_price'], 2) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
