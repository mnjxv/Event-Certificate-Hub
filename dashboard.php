<?php
require "config.php";
require_login();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    // Session points to a user that no longer exists — bail out cleanly.
    header("Location: logout.php");
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM certificates WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare(
    "SELECT COUNT(*) as total FROM certificates WHERE user_id = ? AND validation_status = 'Uploaded'"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$validCount = $stmt->get_result()->fetch_assoc();

// Recent certificates for a quick preview on the dashboard.
$stmt = $conn->prepare(
    "SELECT * FROM certificates WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 3"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent = $stmt->get_result();

$profileUrl = (isset($_SERVER['HTTP_HOST']) ? "//" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) : "") .
    "/public_profile.php?user=" . $user['share_code'];

$page_title = "Dashboard";
$active = "dashboard";
$flashes = get_flashes();
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
    <a href="dashboard.php" class="navbar-brand">
      <span class="brand-mark">EC</span>
      <span class="brand-name">EventCertificateHub</span>
    </a>
    <nav class="navbar-links">
      <a href="dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="upload_certificate.php" class="<?= $active === 'upload' ? 'active' : '' ?>">Upload</a>
      <a href="my_certificate.php" class="<?= $active === 'certificates' ? 'active' : '' ?>">My Certificates</a>
      <a href="search.php" class="<?= $active === 'search' ? 'active' : '' ?>">Search</a>
      <a href="logout.php" class="navbar-logout">Logout</a>
    </nav>
    <button class="navbar-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<?php if (!empty($flashes)): ?>
  <div class="flash-stack" role="status" aria-live="polite">
    <?php foreach ($flashes as $f): ?>
      <div class="flash flash-<?= h($f['type']) ?>"><?= h($f['message']) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="page">

  <div class="dash-hero">
    <div>
      <h1>Welcome back, <?= h($user['full_name']) ?></h1>
      <p>Here's a quick look at your certificate portfolio.</p>
    </div>
    <div class="dash-stats">
      <div class="dash-stat">
        <span class="num"><?= (int)$count['total'] ?></span>
        <span class="label">Total Certificates</span>
      </div>
      <div class="dash-stat">
        <span class="num"><?= (int)$validCount['total'] ?></span>
        <span class="label">Uploaded</span>
      </div>
    </div>
  </div>

  <div class="quick-actions">
    <a href="upload_certificate.php" class="quick-action">
      <span class="qa-title">Upload Certificate</span>
      <span class="qa-desc">Add a new achievement to your portfolio.</span>
    </a>
    <a href="my_certificate.php" class="quick-action">
      <span class="qa-title">My Certificates</span>
      <span class="qa-desc">View and manage everything you've uploaded.</span>
    </a>
    <a href="search.php" class="quick-action">
      <span class="qa-title">Search Achievements</span>
      <span class="qa-desc">Find certificates by date, issuer, or category.</span>
    </a>
  </div>

  <div class="share-box">
    <div>
      <div class="share-label">Your public portfolio link</div>
      <div class="share-link"><?= h($profileUrl) ?></div>
    </div>
    <a href="public_profile.php?user=<?= h($user['share_code']) ?>" class="btn btn-gold btn-sm">View Public Portfolio</a>
  </div>

  <h2 class="section-title">Recent Certificates</h2>
  <?php if ($recent->num_rows === 0): ?>
    <div class="empty-state panel">
      <h3>No certificates yet</h3>
      <p>Upload your first certificate to start building your portfolio.</p>
      <div style="margin-top:16px;">
        <a href="upload_certificate.php" class="btn btn-primary">Upload Certificate</a>
      </div>
    </div>
  <?php else: ?>
    <div class="cert-grid">
      <?php while ($row = $recent->fetch_assoc()): ?>
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