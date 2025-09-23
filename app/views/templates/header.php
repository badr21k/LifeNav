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
    <style>
      :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --primary-light: #dbeafe;
        --primary-gradient: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        --text: #111827;
        --text-light: #6b7280;
        --text-lighter: #9ca3af;
        --card: #ffffff;
        --background: #f8fafc;
        --border: #e5e7eb;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
        --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.1);
        --shadow-lg: 0 20px 40px -10px rgba(0,0,0,0.15);
        --header-h: 72px;
        --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        --radius-sm: .5rem;
        --radius-md: .75rem;
        --radius-lg: 1rem;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      [data-theme="dark"] {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --primary-light: #1e3a8a;
        --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        --text: #f9fafb;
        --text-light: #d1d5db;
        --text-lighter: #9ca3af;
        --card: #111827;
        --background: #0f172a;
        --border: #374151;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
        --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.4);
        --shadow-lg: 0 20px 40px -10px rgba(0,0,0,0.5);
      }
      * {
        box-sizing: border-box;
      }
      body {
        background: var(--background);
        color: var(--text);
        font-family: var(--font-sans);
        padding-top: var(--header-h);
        line-height: 1.6;
      }
      .navbar-modern {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        min-height: var(--header-h);
        backdrop-filter: saturate(180%) blur(20px);
        background: rgba(var(--card-rgb, 255, 255, 255), 0.85) !important;
        border-bottom: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        padding: 0.5rem 0;
      }
      [data-theme="dark"] .navbar-modern {
        background: rgba(17, 24, 39, 0.85) !important;
        backdrop-filter: saturate(180%) blur(20px);
      }
      .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 800;
        font-size: 1.5rem;
        letter-spacing: -0.02em;
        color: var(--text);
        text-decoration: none;
        transition: var(--transition);
      }
      .brand-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--primary-gradient);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        box-shadow: var(--shadow-md);
        transition: var(--transition);
      }
      .navbar-brand:hover .brand-icon {
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
      }
      .nav-link {
        font-weight: 600;
        color: var(--text-light);
        padding: 0.75rem 1.25rem;
        font-size: 0.95rem;
        border-radius: var(--radius-md);
        transition: var(--transition);
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }
      .nav-link i {
        font-size: 1.1rem;
        opacity: 0.9;
        transition: var(--transition);
      }
      .nav-link:hover {
        color: var(--primary);
        background: var(--primary-light);
        transform: translateY(-1px);
      }
      .nav-link:hover i {
        transform: scale(1.1);
      }
      .nav-link.active {
        color: var(--primary);
        background: var(--primary-light);
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
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(33,37,41,0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        transition: var(--transition);
      }
      [data-theme="dark"] .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255,255,255,0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      }
      .user-chip {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border: 1px solid var(--border);
        border-radius: 50px;
        background: var(--card);
        color: var(--text);
        transition: var(--transition);
        text-decoration: none;
      }
      .user-chip:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
      }
      .user-chip i {
        opacity: 0.9;
        font-size: 1.1rem;
      }
      .dropdown-menu {
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        background: var(--card);
        padding: 0.5rem;
        min-width: 200px;
      }
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
      .dropdown-item:hover {
        background: var(--primary-light);
        color: var(--primary);
        transform: translateX(5px);
      }
      @media (max-width: 991.98px) {
        .navbar-collapse {
          position: fixed;
          top: var(--header-h);
          left: 0;
          right: 0;
          background: var(--card);
          border-top: 1px solid var(--border);
          box-shadow: var(--shadow-lg);
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
        .nav-link {
          padding: 1rem 1.25rem;
          border-radius: var(--radius-md);
          border-left: 3px solid transparent;
        }
        .nav-link.active {
          border-left-color: var(--primary);
          background: var(--primary-light);
        }
        .user-chip-mobile {
          width: 100%;
          justify-content: center;
          margin: 1rem 0;
          padding: 1rem;
        }
      }
      @media (min-width: 992px) {
        .navbar .container-fluid {
          display: grid;
          grid-template-columns: auto 1fr auto;
          align-items: center;
          gap: 2rem;
        }
        .navbar-collapse {
          display: flex !important;
          align-items: center;
        }
        .navbar-nav {
          flex: 1;
          justify-content: center;
          gap: 0.5rem;
        }
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
    </style>
</head>
<body>
<div class="mobile-menu-backdrop" id="mobileMenuBackdrop"></div>
<nav class="navbar navbar-expand-lg navbar-modern">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
<script>
  (function(){
    const key = 'lifenav_theme';
    try {
      let saved = localStorage.getItem(key);
      if (!saved) {
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        saved = prefersDark ? 'dark' : 'light';
        localStorage.setItem(key, saved);
      }
      if (saved === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    } catch(_) {}
    window.toggleTheme = function() {
      const cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      if (cur === 'light') document.documentElement.removeAttribute('data-theme');
      else document.documentElement.setAttribute('data-theme', 'dark');
      try { localStorage.setItem(key, cur); } catch(_) {}
    };
    document.addEventListener('DOMContentLoaded', function() {
      const navCollapse = document.getElementById('navbarSupportedContent');
      const toggler = document.querySelector('.navbar-toggler');
      const backdrop = document.getElementById('mobileMenuBackdrop');
      if (!navCollapse) return;
      const bsCollapse = new bootstrap.Collapse(navCollapse, { toggle: false });
      function toggleBackdrop(show) {
        if (backdrop) {
          backdrop.classList.toggle('show', show);
        }
      }
      navCollapse.addEventListener('click', function(e) {
        if (e.target.closest('.nav-link') && window.innerWidth < 992) {
          const link = e.target.closest('.nav-link');
          e.preventDefault();
          const href = link.getAttribute('href');
          setTimeout(() => {
            bsCollapse.hide();
            toggleBackdrop(false);
            window.location.href = href;
          }, 150);
        }
      });
      if (backdrop) {
        backdrop.addEventListener('click', function() {
          bsCollapse.hide();
          toggleBackdrop(false);
        });
      }
      navCollapse.addEventListener('show.bs.collapse', function() {
        toggleBackdrop(true);
        document.body.style.overflow = 'hidden';
      });
      navCollapse.addEventListener('hide.bs.collapse', function() {
        toggleBackdrop(false);
        document.body.style.overflow = '';
      });
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navCollapse.classList.contains('show')) {
          bsCollapse.hide();
          toggleBackdrop(false);
        }
      });
      window.addEventListener('resize', function() {
        if (window.innerWidth >= 992 && navCollapse.classList.contains('show')) {
          bsCollapse.hide();
          toggleBackdrop(false);
        }
      });
      function initDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
          const toggle = dropdown.querySelector('.dropdown-toggle');
          const menu = dropdown.querySelector('.dropdown-menu');
          if (toggle && menu) {
            toggle.addEventListener('keydown', function(e) {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const bsDropdown = bootstrap.Dropdown.getInstance(toggle) || new bootstrap.Dropdown(toggle);
                bsDropdown.toggle();
              }
            });
            menu.addEventListener('click', function(e) {
              if (e.target.closest('.dropdown-item') && window.innerWidth < 992) {
                const bsDropdown = bootstrap.Dropdown.getInstance(toggle);
                if (bsDropdown) bsDropdown.hide();
                if (navCollapse.classList.contains('show')) {
                  bsCollapse.hide();
                  toggleBackdrop(false);
                }
              }
            });
          }
        });
      }
      initDropdowns();
    });
  })();
</script>
</body>
</html>