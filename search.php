<?php
require "config.php";
require_login();

$stmt = $conn->query("SELECT * FROM categories ORDER BY category_name");
$categories = $stmt->fetch_all(MYSQLI_ASSOC);

$result = null;
$searched = false;
$date = $issuer = $category = "";
$sort = "date_desc";

if (isset($_POST['search'])) {
    $searched = true;
    $date = trim($_POST['date'] ?? '');
    $issuer = trim($_POST['issuer'] ?? '');
    $category = $_POST['category'] ?? '';
    $sort = $_POST['sort'] ?? 'date_desc';

    $sql = "SELECT certificates.*, users.full_name, categories.category_name
            FROM certificates
            JOIN users ON certificates.user_id = users.user_id
            JOIN categories ON certificates.category_id = categories.category_id";

    $conditions = [];
    $params = [];
    $types = "";

    if ($date !== '') {
        $conditions[] = "certificates.event_date = ?";
        $params[] = $date;
        $types .= "s";
    }
    if ($issuer !== '') {
        $conditions[] = "certificates.issuer LIKE ?";
        $params[] = "%{$issuer}%";
        $types .= "s";
    }
    if ($category !== '') {
        $conditions[] = "certificates.category_id = ?";
        $params[] = (int)$category;
        $types .= "i";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sort_options = [
        'date_desc'   => 'certificates.event_date DESC',
        'date_asc'    => 'certificates.event_date ASC',
        'issuer_asc'  => 'certificates.issuer ASC',
        'issuer_desc' => 'certificates.issuer DESC',
    ];
    $order_by = $sort_options[$sort] ?? $sort_options['date_desc'];
    $sql .= " ORDER BY " . $order_by;

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}

$page_title = "Search Certificates";
$active = "search";
$show_owner = true;
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
    <h1>Search Certificates</h1>
    <p>Find achievements across EventCertificateHub by date, issuer, or category.</p>
  </div>

  <form method="POST" class="search-form">
    <div class="field">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" value="<?= h($date) ?>">
    </div>
    <div class="field">
      <label for="issuer">Issuer</label>
      <input type="text" id="issuer" name="issuer" placeholder="Search issuer" value="<?= h($issuer) ?>">
    </div>
    <div class="field">
      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int)$cat['category_id'] ?>" <?= ((string)$category === (string)$cat['category_id']) ? 'selected' : '' ?>>
            <?= h($cat['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="sort">Sort By</label>
      <select id="sort" name="sort">
        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Newest first</option>
        <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Oldest first</option>
        <option value="issuer_asc" <?= $sort === 'issuer_asc' ? 'selected' : '' ?>>Issuer A–Z</option>
        <option value="issuer_desc" <?= $sort === 'issuer_desc' ? 'selected' : '' ?>>Issuer Z–A</option>
      </select>
    </div>
    <button type="submit" name="search" class="btn btn-primary">Search</button>
  </form>

  <?php if ($searched): ?>
    <?php if ($result && $result->num_rows > 0): ?>
      <div class="cert-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
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
              <?php if (!empty($row['full_name'])): ?>
                <p class="cert-meta"><span class="meta-label">By</span> <?= h($row['full_name']) ?></p>
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
    <?php else: ?>
      <div class="empty-state panel">
        <h3>No results found</h3>
        <p>Try a different date, issuer, or category.</p>
        <div style="margin-top:16px;">
          <a href="search.php" class="btn btn-outline">Clear Filters</a>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<footer class="site-footer">
  <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, organizing, and sharing your achievements.</p>
</footer>
<script src="js/app.js"></script>
</body>
</html>