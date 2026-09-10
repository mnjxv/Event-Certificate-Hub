<?php
session_start();

// Clear all session data, then destroy the session itself and its cookie.
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Start a fresh session just to carry the "logged out" flash message.
session_start();
$_SESSION['flash'][] = ['type' => 'info', 'message' => 'You have been logged out.'];

header("Location: login.php");
exit;
