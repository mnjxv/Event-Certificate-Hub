<?php
require "config.php";
require_login();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT certificates.*, categories.category_name
     FROM certificates
     JOIN categories ON certificates.category_id = categories.category_id
     WHERE certificates.user_id = ?
     ORDER BY certificates.uploaded_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result();

$page_title = "My Certificates";
$active = "certificates";
$show_actions = true;
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
  <div class="page-header">
    <h1>My Certificates</h1>
    <p>All the achievements you've uploaded to EventCertificateHub.</p>
  </div>

  <?php if ($data->num_rows === 0): ?>
    <div class="empty-state panel">
      <h3>No certificates yet</h3>
      <p>Upload your first certificate to start building your portfolio.</p>
      <div style="margin-top:16px;">
        <a href="upload_certificate.php" class="btn btn-primary">Upload Certificate</a>
      </div>
    </div>
  <?php else: ?>
    <div class="cert-grid">
      <?php while ($row = $data->fetch_assoc()): ?>
        <?php
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
              <form method="POST" action="delete_certificate.php" style="display:inline;" onsubmit="return confirmDelete();">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)($row['certificate_id'] ?? 0) ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
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