<?php
require "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$name = "";
$email = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $errors[] = "Your session expired. Please try again.";
    }
    if ($name === '' || $email === '' || $password === '') {
        $errors[] = "Please fill in all fields.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Check for an existing account with this email before inserting.
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $errors[] = "An account with that email already exists.";
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $code = "USER" . bin2hex(random_bytes(6));

        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, password, share_code) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $hash, $code);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            flash('success', 'Account created! Welcome to EventCertificateHub.');
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Something went wrong creating your account. Please try again.";
        }
    }
}

$page_title = "Sign Up";
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
    <h2>Create your account</h2>
    <p class="sub">Start building your certificate portfolio.</p>

    <?php if (!empty($errors)): ?>
      <div class="flash flash-error" style="margin-bottom:16px;">
        <?php foreach ($errors as $e): ?>
          <div><?= h($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

      <div class="field">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Juan Dela Cruz"
               value="<?= h($name) ?>" required>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?= h($email) ?>" required>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="At least 8 characters" required>
      </div>

      <div class="field">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
      </div>

      <button type="submit" name="register" class="btn btn-primary btn-block btn-lg">Sign Up</button>
    </form>

    <p class="auth-footer">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</div>

<footer class="site-footer">
  <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, validating, and sharing your achievements.</p>
</footer>
<script src="js/app.js"></script>
</body>
</html>