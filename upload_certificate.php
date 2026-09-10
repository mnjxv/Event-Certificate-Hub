<?php
require "config.php";
require_login();

$errors = [];

$stmt = $conn->query("SELECT * FROM categories ORDER BY category_name");
$categories = $stmt->fetch_all(MYSQLI_ASSOC);

if (isset($_POST['upload'])) {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $issuer = trim($_POST['issuer'] ?? '');
    $date = $_POST['date'] ?? '';
    $category = (int)($_POST['category'] ?? 0);

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $errors[] = "Your session expired. Please try again.";
    }
    if ($title === '' || $issuer === '' || $date === '' || $category <= 0) {
        $errors[] = "Please fill in all fields.";
    }
    if (empty($_FILES['file']['name'])) {
        $errors[] = "Please choose a PDF file to upload.";
    }

    if (empty($errors)) {
        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed. Please try again.";
        } elseif ($file['size'] > MAX_UPLOAD_BYTES) {
            $errors[] = "File is too large. Maximum size is 5 MB.";
        } else {
            $tmp = $file['tmp_name'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($tmp);

            // Check both the real MIME type and the extension — relying on
            // either alone is easy to spoof, but requiring both to agree
            // makes that much harder.
            if ($mime !== "application/pdf" || $ext !== "pdf") {
                $errors[] = "Only PDF files are accepted.";
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }

                $safe_name = time() . "_" . bin2hex(random_bytes(8)) . ".pdf";
                $destination = UPLOAD_DIR . $safe_name;

                if (move_uploaded_file($tmp, $destination)) {
                    // No manual review step exists in this app — every successful
                    // upload is simply marked "Uploaded" rather than implying a
                    // verification process that doesn't actually happen.
                    $status = "Uploaded";
                    $stmt = $conn->prepare(
                        "INSERT INTO certificates
                         (user_id, category_id, title, issuer, event_date, certificate_file, validation_status)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param("iisssss", $user_id, $category, $title, $issuer, $date, $safe_name, $status);
                    $stmt->execute();

                    flash('success', 'Certificate uploaded successfully.');
                    header("Location: my_certificate.php");
                    exit;
                } else {
                    $errors[] = "Could not save the uploaded file. Please try again.";
                }
            }
        }
    }
}

$page_title = "Upload Certificate";
$active = "upload";
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

<div class="page" style="max-width:560px;">
  <div class="page-header">
    <h1>Upload Certificate</h1>
    <p>Add a new achievement to your portfolio. PDF files only.</p>
  </div>

  <div class="panel">
    <?php if (!empty($errors)): ?>
      <div class="flash flash-error" style="margin-bottom:16px;">
        <?php foreach ($errors as $e): ?>
          <div><?= h($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

      <div class="field">
        <label for="title">Certificate Title</label>
        <input type="text" id="title" name="title" placeholder="e.g. Introduction to Visual Graphic Design"
               value="<?= h($_POST['title'] ?? '') ?>" required>
      </div>

      <div class="field">
        <label for="issuer">Issuer / Organization</label>
        <input type="text" id="issuer" name="issuer" placeholder="e.g. TESDA Online Program"
               value="<?= h($_POST['issuer'] ?? '') ?>" required>
      </div>

      <div class="field">
        <label for="date">Event Date</label>
        <input type="date" id="date" name="date" value="<?= h($_POST['date'] ?? '') ?>" required>
      </div>

      <div class="field">
        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="">Select a category</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['category_id'] ?>"
              <?= (isset($_POST['category']) && (int)$_POST['category'] === (int)$cat['category_id']) ? 'selected' : '' ?>>
              <?= h($cat['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="file">Certificate File (PDF, max 5 MB)</label>
        <input type="file" id="file" name="file" accept=".pdf,application/pdf" onchange="showPDF(this)" required>
        <div id="fileName" style="margin-top:6px; font-size:13.5px; color:var(--muted);"></div>
      </div>

      <button type="submit" name="upload" class="btn btn-primary btn-block btn-lg">Upload</button>
    </form>
  </div>
</div>

<footer class="site-footer">
  <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, organizing, and sharing your achievements.</p>
</footer>
<script src="js/app.js"></script>
</body>
</html>