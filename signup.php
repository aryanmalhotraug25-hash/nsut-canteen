<?php
require_once 'auth.php';
include 'db_connect.php';

$errors = [];
$name = $email = $role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // 'staff' (canteen staff) is NOT allowed here - those accounts are created by the admin in the database.
    $allowed_roles = ['student', 'faculty'];

    if ($name === '' || strlen($name) > 100)         $errors[] = 'Enter your name (max 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) $errors[] = 'Enter a valid email address.';
    if (!in_array($role, $allowed_roles, true))      $errors[] = 'Choose a valid role.';
    if (strlen($password) < 8)                       $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)                      $errors[] = 'Passwords do not match.';

    // Is the email already registered?
    if (!$errors) {
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'An account with this email already exists. Log in instead.';
        }
        $stmt->close();
    }

    // Safely insert the new user (prepared statement + hashed password)
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $role, $hash);
        $stmt->execute();
        $stmt->close();

        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create account - NSUT Canteen</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card auth-card">
    <h1>Create your account</h1>

    <?php foreach ($errors as $err): ?>
      <div class="msg error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="signup.php" novalidate>
      <label for="name">Full name</label>
      <input type="text" id="name" name="name" value="<?= e($name) ?>" required>

      <label for="email">College email</label>
      <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

      <label for="role">I am a</label>
      <select id="role" name="role" required>
        <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
        <option value="faculty" <?= $role === 'faculty' ? 'selected' : '' ?>>Faculty</option>
      </select>

      <label for="password">Password (8+ characters)</label>
      <input type="password" id="password" name="password" minlength="8" required>

      <label for="confirm_password">Confirm password</label>
      <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>

      <button type="submit">Create account</button>
    </form>

    <p class="alt">Already registered? <a href="login.php">Log in</a></p>
  </div>
</body>
</html>
