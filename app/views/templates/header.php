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
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="/home">LifeNav</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <!-- Left -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link<?= $active('home') ?>" href="/home">Home</a>
        </li>


        <!-- Essentials module -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link<?= $active('essentials') ?>" href="/essentials">Essentials</a>
        </li>

          <li class="nav-item">
            <a class="nav-link<?= $active('finance') ?>" href="/finance">Finance</a>
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
