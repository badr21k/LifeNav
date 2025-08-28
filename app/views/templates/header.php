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
        <li class="nav-item"><a class="nav-link" href="/mode/normal">Normal</a></li>
        <li class="nav-item"><a class="nav-link" href="/mode/travel">Travel</a></li>
        <li class="nav-item"><a class="nav-link" href="/reports/overview">Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="/settings/currency">Currency</a></li>


        <!-- LifeNav module -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $ctrl === 'lifenav' ? ' active' : '' ?>" href="#"
             id="navLifeNav" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            LifeNav
          </a>
          <ul class="dropdown-menu" aria-labelledby="navLifeNav">
            <li><a class="dropdown-item<?= $active('lifenav','index') ?>" href="/lifenav/normal">Normal Mode</a></li>
            <li><a class="dropdown-item<?= $active('lifenav','travel') ?>" href="/lifenav/travel">Travel Mode</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item<?= $active('lifenav','reports_monthly') ?>" href="/lifenav/reports/monthly">Reports</a></li>
          </ul>
        </li>

        <!-- Essentials module (legacy) -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $ctrl === 'essentials' ? ' active' : '' ?>" href="#"
             id="navEssentials" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Essentials
          </a>
          <ul class="dropdown-menu" aria-labelledby="navEssentials">
            <li><a class="dropdown-item<?= $active('essentials','index') ?>" href="/essentials">List</a></li>
            <li><a class="dropdown-item<?= $active('essentials','create') ?>" href="/essentials/create">Add expense</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item<?= $active('essentials','import') ?>" href="/essentials/import">Import CSV</a></li>
            <li><a class="dropdown-item<?= $active('essentials','reports_monthly') ?>" href="/essentials/reports/monthly">Monthly totals</a></li>
            <li><a class="dropdown-item<?= $active('essentials','reports_trend') ?>" href="/essentials/reports/trend">Trend</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link<?= $active('about') ?>" href="/about">About</a>
        </li>

      </ul>

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
