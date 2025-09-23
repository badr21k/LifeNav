<?php
if (!isset($_SESSION['auth'])) {
    header('Location: /login');
    exit;
}
$ctrl   = $_SESSION['controller'] ?? '';
$method = $_SESSION['method'] ?? '';
$active = function(string $c, ?string $m = null) use ($ctrl, $method) {
    if ($m === null) return $ctrl === $c ? ' active' : '';
    return ($ctrl === $c && $method === $m) ? ' active' : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?>LifeNav</title>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" href="/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      :root{
        --primary:#2c6b5f; --text:#111827; --text-light:#6b7280; --card:#fff; --border:#e5e7eb;
      }
      .navbar.navbar-modern{ backdrop-filter:saturate(1.2) blur(6px); background:linear-gradient(145deg, rgba(255,255,255,.86), rgba(255,255,255,.72)) !important; border-bottom:1px solid var(--border); }
      .navbar .navbar-brand{ display:flex; align-items:center; gap:.5rem; font-weight:800; letter-spacing:-.01em; color:var(--text); }
      .navbar .brand-icon{ width:28px; height:28px; border-radius:8px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; box-shadow:0 2px 8px rgba(44,107,95,.2); }
      .navbar .nav-link{ font-weight:600; color:var(--text-light); border-radius:10px; padding:.5rem .75rem; }
      .navbar .nav-link.active, .navbar .nav-link:hover{ color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(44,107,95,.25); }
      .navbar .navbar-text{ color:var(--text); font-weight:600; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light navbar-modern">
  <div class="container-fluid">
    <a class="navbar-brand" href="/home">
      <span class="brand-icon">LN</span>
      <span>LifeNav</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <!-- Left -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?= $active('home') ?>" href="/home"><i class="fa-solid fa-house"></i> Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('essentials') ?>" href="/essentials"><i class="fa-solid fa-receipt"></i> Spending</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('finance') ?>" href="/finance"><i class="fa-solid fa-sack-dollar"></i> Earnings</a>
        </li>

      <!-- Right -->
      <div class="d-flex align-items-center">
        <span class="navbar-text me-2">
          <?= htmlspecialchars($_SESSION['auth']['name'] ?? ($_SESSION['auth']['email'] ?? '')) ?>
        </span>
        <a class="btn btn-outline-secondary btn-sm" href="/logout">Logout</a>
      </div>
    </div>
  </div>
</nav>
