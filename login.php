<?php
require "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$email = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $errors[] = "Your session expired. Please try again.";
    } elseif ($email === '' || $password === '') {
        $errors[] = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Prevent session fixation by issuing a fresh session id on login.
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Incorrect email or password.";
        }
    }
}

$page_title = "Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> · EventCertificateHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-shell">
  <div class="auth-card">
    <a href="index.php" class="brand-link">
      <span class="brand-mark">EC</span> EventCertificateHub
    </a>
    <h2>Welcome back</h2>
    <p class="sub">Log in to access your certificates and portfolio.</p>

    <?php if (!empty($errors)): ?>
      <div class="flash flash-error" style="margin-bottom:16px;">
        <?= h(implode(' ', $errors)) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?= h($email) ?>" required>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" required>
      </div>

      <button type="submit" name="login" class="btn btn-primary btn-block btn-lg">Login</button>
    </form>

    <p class="auth-footer">Don't have an account? <a href="signup.php">Sign up</a></p>
  </div>
</div>

<footer class="site-footer">
  <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, validating, and sharing your achievements.</p>
</footer>
<script src="js/app.js"></script>
</body>
</html>