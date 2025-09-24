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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" href="/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
      // Apply theme BEFORE CSS paints to prevent white flash between pages
      (function(){
        try {
          var key = 'lifenav_theme';
          var saved = localStorage.getItem(key);
          if (!saved) {
            var sysDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            saved = sysDark ? 'dark' : 'light';
          }
          if (saved === 'dark') { document.documentElement.setAttribute('data-theme','dark'); document.documentElement.style.colorScheme='dark'; }
          else { document.documentElement.removeAttribute('data-theme'); document.documentElement.style.colorScheme='light'; }
          // Suppress transitions during first paint; footer removes this class on DOMContentLoaded
          document.documentElement.classList.add('theme-init');
        } catch(_) {}
      })();
    </script>
    <style>
      /* Disable transitions during initial theme application to prevent flicker */
      html.theme-init *, html.theme-init *::before, html.theme-init *::after { transition: none !important; }
      :root {
        --primary: #2c6b5f;
        --primary-dark: #1f4b43;
        --primary-light: #e6f0ee;
        --text: #111827;
        --text-light: #6b7280;
        --card: #ffffff;
        --card-rgb: 255, 255, 255;
        --background: #f8fafc;
        --border: #e5e7eb;
        --shadow-sm: 0 2px 4px rgba(0,0,0,.06);
        --shadow-md: 0 10px 20px rgba(2,6,12,.08);
        --header-h: 72px;
        --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        --radius-sm: .5rem;
        --radius-md: .75rem;
        --radius-lg: 1rem;
        --transition: all .25s cubic-bezier(.4,0,.2,1);
      }
      [data-theme="dark"] {
        /* Brighter primary with softer light background tint */
        --primary: #6fd5c7;
        --primary-dark: #4cb8a9;
        --primary-light: rgba(111, 213, 199, 0.14);
        /* Text and surfaces */
        --text: #e5e7eb;
        --text-light: #9ca3af;
        --card: #111827;
        --card-rgb: 17, 24, 39; /* for glass header */
        --background: #0b1220;
        --border: #22304d;
        /* Slightly stronger but still soft shadows for dark */
        --shadow-sm: 0 2px 6px rgba(0,0,0,.5);
        --shadow-md: 0 12px 28px rgba(0,0,0,.6);
      }
      * {
        box-sizing: border-box;
      }
      body {
        background: var(--background);
        color: var(--text);
        font-family: var(--font-sans);
        /* Global offset so content clears the fixed header */
        padding-top: calc(var(--header-h) + 1rem);
        line-height: 1.6;
        overflow-x: hidden;
      }
      .navbar-modern {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        min-height: var(--header-h);
        backdrop-filter: saturate(180%) blur(10px);
        background: rgba(var(--card-rgb, 255, 255, 255), 0.85) !important;
        border-bottom: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        padding: 0.5rem 0;
      }
      [data-theme="dark"] .navbar-modern {
        background: rgba(var(--card-rgb), 0.88) !important;
        backdrop-filter: saturate(180%) blur(10px);
      }
      .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 800;
        font-size: 1.4rem;
        letter-spacing: -0.02em;
        color: var(--text);
        text-decoration: none;
        transition: var(--transition);
      }
      .brand-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-sm);
        background: var(--primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(44,107,95,.2);
        transition: var(--transition);
      }
      .navbar-brand:hover .brand-icon {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(44,107,95,.3);
      }
      .nav-link {
        font-weight: 600;
        color: var(--text-light);
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        border-radius: var(--radius-md);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
      }
      .nav-link i {
        font-size: 1.1rem;
        opacity: 0.9;
        transition: var(--transition);
      }
      .nav-link:hover { color: var(--primary); background: transparent; transform: translateY(-1px); }
      [data-theme="dark"] .nav-link:hover { background: rgba(111,213,199,0.08); }
      .nav-link:hover i {
        transform: scale(1.1);
      }
      .nav-link.active {
        color: var(--primary);
        background: transparent;
        font-weight: 700;
      }
      .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 50%;
        transform: translateX(-50%);
        width: 20px;
        height: 3px;
        background: var(--primary);
        border-radius: 2px;
      }
      .navbar-toggler {
        border: 1px solid var(--border);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        transition: var(--transition);
      }
      .navbar-toggler:focus {
        box-shadow: 0 0 0 2px var(--primary-light);
      }
      .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(44,107,95,0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        transition: var(--transition);
      }
      [data-theme="dark"] .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(76,168,155,0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      }
      .user-chip {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        padding: 0.5rem 0.875rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--card);
        color: var(--text);
        transition: var(--transition);
      }
      .user-chip:hover { border-color: var(--primary); background: var(--primary-light); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
      [data-theme="dark"] .user-chip:hover { background: rgba(111,213,199,0.12); }
      .user-chip i {
        opacity: 0.9;
        font-size: 1.1rem;
      }
      .dropdown-menu {
        z-index: 4000;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        background: var(--card);
        padding: 0.5rem;
        min-width: 200px;
      }
      [data-theme="dark"] .dropdown-menu { background: var(--card); border-color: var(--border); }
      .dropdown-item {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-sm);
        font-weight: 500;
        color: var(--text);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }
      .dropdown-item:hover, .dropdown-item:focus {
        background: var(--primary-light);
        color: var(--primary);
        transform: translateX(5px);
      }
      .mobile-menu-backdrop {
        position: fixed;
        top: var(--header-h);
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1029;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease-in-out;
      }
      .mobile-menu-backdrop.show {
        opacity: 1;
        pointer-events: all;
      }
      @media (max-width: 991.98px) {
        .navbar-collapse {
          position: fixed;
          top: var(--header-h);
          left: 0;
          right: 0;
          background: var(--card);
          border-top: 1px solid var(--border);
          box-shadow: var(--shadow-md);
          max-height: calc(100vh - var(--header-h));
          overflow-y: auto;
          padding: 1rem;
          transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }
        .navbar-collapse:not(.show) {
          transform: translateY(-100%);
          opacity: 0;
          pointer-events: none;
        }
        .navbar-collapse.show {
          transform: translateY(0);
          opacity: 1;
        }
        .nav-item {
          margin-bottom: 0.5rem;
        }
        .nav-link { padding: 1rem 1.25rem; border-radius: var(--radius-md); }
        .nav-link.active { background: transparent; }
        .user-chip-mobile { width: 100%; justify-content: center; margin: 0.5rem 0 0.25rem; }
      }
      @media (min-width: 992px) {
        .navbar .container-fluid {
          display: grid;
          grid-template-columns: auto 1fr auto;
          align-items: center;
          gap: 1.5rem;
        }
        .navbar-collapse {
          display: flex !important;
          align-items: center;
        }
        .navbar-nav {
          flex: 1;
          justify-content: center;
          gap: 1.25rem;
        }
      }

      /* Focus-visible rings for accessibility */
      .nav-link:focus-visible,
      .user-chip:focus-visible,
      .dropdown-item:focus-visible,
      .navbar-toggler:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
      }

      /* Scroll-aware glass effect */
      .navbar-modern.scrolled {
        box-shadow: var(--shadow-md);
      }

      /* Reduced motion support */
      @media (prefers-reduced-motion: reduce) {
        * { transition: none !important; animation: none !important; }
      }
    </style>
</head>
<body>
<div class="mobile-menu-backdrop" id="mobileMenuBackdrop"></div>
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
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?= $active('home') ?>" href="/home">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('essentials') ?>" href="/essentials">
            <i class="fa-solid fa-receipt"></i>
            <span>Spending</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('finance') ?>" href="/finance">
            <i class="fa-solid fa-sack-dollar"></i>
            <span>Earnings</span>
          </a>
        </li>
        <li class="nav-item d-lg-none">
          <div class="dropdown">
            <button class="btn user-chip user-chip-mobile dropdown-toggle w-100" type="button" 
                    id="userMenuMobile" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-solid fa-user"></i>
              <?= htmlspecialchars($_SESSION['auth']['name'] ?? ($_SESSION['auth']['email'] ?? '')) ?>
            </button>
            <ul class="dropdown-menu w-100" aria-labelledby="userMenuMobile">
              <li><button class="dropdown-item" type="button" onclick="toggleTheme()">
                <i class="fa-solid fa-moon"></i>Toggle Dark Mode
              </button></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/logout">
                <i class="fa-solid fa-right-from-bracket"></i>Logout
              </a></li>
            </ul>
          </div>
        </li>
      </ul>
    </div>
    <div class="d-none d-lg-flex align-items-center">
      <div class="dropdown">
        <button class="btn user-chip dropdown-toggle" type="button" 
                id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-solid fa-user"></i>
          <?= htmlspecialchars($_SESSION['auth']['name'] ?? ($_SESSION['auth']['email'] ?? '')) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
          <li><button class="dropdown-item" type="button" onclick="toggleTheme()">
            <i class="fa-solid fa-moon"></i>Toggle Dark Mode
          </button></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="/logout">
            <i class="fa-solid fa-right-from-bracket"></i>Logout
          </a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
