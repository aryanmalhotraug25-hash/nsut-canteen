<?php
require_once 'auth.php';
include 'db_connect.php';

// Already logged in? Send them to their home page.
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . home_for_role($_SESSION['role'] ?? 'student'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT user_id, name, role, password FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $valid = false;
    if ($user) {
        if (password_get_info($user['password'])['algo'] !== null) {
            // Normal case: password stored as a hash
            $valid = password_verify($password, $user['password']);
        } else {
            // Old seed accounts (Rahul / Canteen Admin) have plain-text passwords.
            // Accept once, then upgrade them to a hash automatically.
            if (hash_equals($user['password'], $password)) {
                $valid = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = $conn->prepare('UPDATE users SET password = ? WHERE user_id = ?');
                $up->bind_param('si', $newHash, $user['user_id']);
                $up->execute();
                $up->close();
            }
        }
    }

    if ($valid) {
        session_regenerate_id(true);   // prevents session fixation
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        header('Location: ' . home_for_role($user['role']));
        exit;
    }
    // Same message whether email or password was wrong
    $error = 'Incorrect email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in - NSUT Canteen</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card auth-card">
    <h1>Log in</h1>

    <?php if (!empty($_GET['registered'])): ?>
      <div class="msg success">Account created. Log in to start ordering.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="msg error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Log in</button>
    </form>

    <p class="alt">New here? <a href="signup.php">Create an account</a></p>
  </div>
</body>
</html>
