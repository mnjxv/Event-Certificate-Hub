<?php
require "config.php";

// Already logged in? Skip the landing page and go straight to the dashboard.
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Welcome";
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

<header class="navbar landing-navbar">
  <div class="navbar-inner">
    <a href="index.php" class="navbar-brand" aria-label="EventCertificateHub home">
      <span class="brand-mark" aria-hidden="true">EC</span>
      <span class="brand-name">EventCertificateHub</span>
    </a>
    <nav class="nav-actions" aria-label="Account navigation">
      <a href="login.php" class="btn btn-outline btn-sm">Login</a>
      <a href="signup.php" class="btn btn-primary btn-sm">Sign Up</a>
    </nav>
  </div>
</header>

<section class="hero">
  <h1>Store, organize, and showcase your <span class="accent">event certificates</span> in one place.</h1>
  <p class="lead">A digital platform to store, organize, and showcase your event certificates and achievements.</p>
  <div class="hero-actions">
    <a href="signup.php" class="btn btn-primary btn-lg">Get Started — Sign Up</a>
    <a href="login.php" class="btn btn-outline btn-lg">Login</a>
  </div>
</section>

<section class="mockup-wrap">
  <div class="mockup">
    <div class="mockup-titlebar">
      <span class="dot"></span><span class="dot"></span><span class="dot"></span>
      <span class="mockup-url">eventcertificatehub.app/dashboard</span>
    </div>
    <div class="mockup-body">
      <div class="mockup-hero">
        <div>
          <div class="mockup-line w-160"></div>
          <div class="mockup-line w-220 muted"></div>
        </div>
        <div class="mockup-stats">
          <div class="mockup-stat"><span class="num">12</span><span class="label">Total</span></div>
          <div class="mockup-stat"><span class="num">12</span><span class="label">Uploaded</span></div>
        </div>
      </div>
      <div class="mockup-cards">
        <div class="mockup-card">
          <div class="mockup-seal">&#10003;</div>
          <div class="mockup-line w-120"></div>
          <div class="mockup-line w-90 muted"></div>
          <div class="mockup-line w-70 muted"></div>
        </div>
        <div class="mockup-card">
          <div class="mockup-seal">&#10003;</div>
          <div class="mockup-line w-130"></div>
          <div class="mockup-line w-90 muted"></div>
          <div class="mockup-line w-70 muted"></div>
        </div>
        <div class="mockup-card">
          <div class="mockup-seal">&#10003;</div>
          <div class="mockup-line w-110"></div>
          <div class="mockup-line w-90 muted"></div>
          <div class="mockup-line w-70 muted"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="features">
  <h2 class="section-heading">Everything you need to manage your achievements</h2>
  <div class="features-grid">
    <div class="feature-card">
      <h3>Upload Certificates</h3>
      <p>Add your event, workshop, seminar, and training certificates as PDF files in seconds.</p>
    </div>
    <div class="feature-card">
      <h3>Verified File Uploads</h3>
      <p>Every upload is checked to confirm it's a genuine PDF file before it's saved to your profile.</p>
    </div>
    <div class="feature-card">
      <h3>Search Achievements</h3>
      <p>Find any certificate across the platform by date, issuer, or category.</p>
    </div>
    <div class="feature-card">
      <h3>Public Portfolio</h3>
      <p>Showcase your achievements on a clean, shareable public profile page.</p>
    </div>
    <div class="feature-card">
      <h3>Shareable Profile Link</h3>
      <p>Get a unique link to your portfolio you can share with employers, schools, or peers.</p>
    </div>
  </div>
</section>

<footer class="site-footer">
  <p>&copy; <?= date("Y") ?> EventCertificateHub. Built for storing, organizing, and sharing your achievements.</p>
</footer>
<script src="js/app.js"></script>
</body>
</html>
