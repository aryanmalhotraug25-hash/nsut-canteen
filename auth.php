<?php
// auth.php - session helpers. Include at the top of every protected page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirects to login.php if nobody is logged in. Returns the logged-in user's ID.
function require_login(): int {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    return (int) $_SESSION['user_id'];
}

// Only lets users with the given role through (used for the staff dashboard).
function require_role(string $role): int {
    $uid = require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        die("<div style='font-family:sans-serif;padding:20px'>Access denied. This page is for canteen staff only. <a href='index.php'>Back to menu</a></div>");
    }
    return $uid;
}

// Safely print text inside HTML.
function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Where each role lands after login.
function home_for_role(string $role): string {
    return $role === 'staff' ? 'dashboard.php' : 'index.php';
}
