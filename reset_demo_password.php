<?php
/* aryana@example.com / password123
juan@example.com   / password123 */

require "config.php";

$password = "password123";
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email IN ('aryana@example.com', 'juan@example.com')");
$stmt->bind_param("s", $hash);
$stmt->execute();

echo "Demo account passwords reset. You can now log in with:\n";
echo "  aryana@example.com / password123\n";
echo "  juan@example.com   / password123\n";
echo "\nDelete this file (reset_demo_password.php) now that it's done its job.\n";
