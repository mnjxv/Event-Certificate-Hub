<?php
require "config.php";

$code = trim($_GET['user'] ?? '');

if ($code === '') {
    http_response_code(404);
    $page_title = "Profile Not Found";
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
    <header class="navbar">
      <div class="navbar-inner">
        <a href="index.php" class="navbar-brand">
          <span class="brand-mark">EC</span>
          <span class="brand-name">EventCertificateHub</span>
        </a>
      </div>
    </header>
    <div class="page">
      <div class="empty-state panel">
        <h3>Profile not found</h3>
        <p>No share code was provided.</p>
        <div style="margin-top:16px; display:flex; gap:10px; justify-content:center;">
          <a href="index.php" class="btn btn-outline">Go Home</a>
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <footer class="site-footer">
      <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, organizing, and sharing your achievements.</p>
    </footer>
    <script src="js/app.js"></script>
    </body>
    </html>
    <?php
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE share_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    $page_title = "Profile Not Found";
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
    <header class="navbar">
      <div class="navbar-inner">
        <a href="index.php" class="navbar-brand">
          <span class="brand-mark">EC</span>
          <span class="brand-name">EventCertificateHub</span>
        </a>
      </div>
    </header>
    <div class="page">
      <div class="empty-state panel">
        <h3>Profile not found</h3>
        <p>This share link doesn't match any account.</p>
        <div style="margin-top:16px; display:flex; gap:10px; justify-content:center;">
          <a href="index.php" class="btn btn-outline">Go Home</a>
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <footer class="site-footer">
      <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, organizing, and sharing your achievements.</p>
    </footer>
    <script src="js/app.js"></script>
    </body>
    </html>
    <?php
    exit;
}

$stmt = $conn->prepare(
    "SELECT certificates.*, categories.category_name
     FROM certificates
     JOIN categories ON certificates.category_id = categories.category_id
     WHERE certificates.user_id = ?
     ORDER BY certificates.event_date DESC"
);
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();
$certificates = $stmt->get_result();

$initial = strtoupper(substr($user['full_name'], 0, 1));
$page_title = $user['full_name'] . "'s Portfolio";
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

<header class="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="navbar-brand">
      <span class="brand-mark">EC</span>
      <span class="brand-name">EventCertificateHub</span>
    </a>
  </div>
</header>

<div class="page">
  <div class="profile-hero">
    <div class="profile-avatar"><?= h($initial) ?></div>
    <h1><?= h($user['full_name']) ?>'s Portfolio</h1>
    <p>Public certificate showcase</p>
  </div>

  <h2 class="section-title">Achievements</h2>

  <?php if ($certificates->num_rows === 0): ?>
    <div class="empty-state panel">
      <h3>No achievements shared yet</h3>
      <p>This user hasn't uploaded any certificates.</p>
    </div>
  <?php else: ?>
    <div class="cert-grid">
      <?php while ($row = $certificates->fetch_assoc()): ?>
        <?php
          $is_uploaded = true; // every successfully saved upload reaches this state
          $file_url = UPLOAD_URL . rawurlencode($row['certificate_file'] ?? '');
        ?>
        <div class="cert-card">
          <div class="cert-seal seal-uploaded" title="Uploaded">
            &#10003;
          </div>
          <div class="cert-body">
            <h3 class="cert-title"><?= h($row['title'] ?? '') ?></h3>
            <p class="cert-meta"><span class="meta-label">Issuer</span> <?= h($row['issuer'] ?? '') ?></p>
            <p class="cert-meta"><span class="meta-label">Date</span> <?= h(date('M j, Y', strtotime($row['event_date'] ?? 'now'))) ?></p>
            <?php if (!empty($row['category_name'])): ?>
              <p class="cert-meta"><span class="meta-label">Category</span> <?= h($row['category_name']) ?></p>
            <?php endif; ?>
            <span class="status-badge status-uploaded">
              Uploaded
            </span>
            <div class="cert-actions">
              <a href="<?= h($file_url) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">View PDF</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<footer class="site-footer">
  <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, organizing, and sharing your achievements.</p>
</footer>
<script src="js/app.js"></script>
</body>
</html>