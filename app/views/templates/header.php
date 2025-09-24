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
      // Apply theme before CSS paints to prevent white flash
      (function(){
        try {
          const key = 'lifenav_theme';
          let saved = localStorage.getItem(key);
          if (!saved) {
            const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            saved = sysDark ? 'dark' : 'light';
          }
          document.documentElement.setAttribute('data-theme', saved);
          document.documentElement.style.colorScheme = saved === 'light' ? 'light' : 'dark';
          document.documentElement.classList.add('theme-init');

          // Update theme toggle buttons
          const updateThemeChecks = () => {
            document.querySelectorAll('.dropdown-item[id^="theme-"]').forEach(btn => {
              btn.classList.toggle('active', btn.id === `theme-${saved}`);
            });
            document.querySelectorAll('.dropdown-item[id^="m-theme-"]').forEach(btn => {
              btn.classList.toggle('active', btn.id === `m-theme-${saved}`);
            });
          };
          window.addEventListener('DOMContentLoaded', updateThemeChecks);

          // Theme switcher function
          window.setTheme = (theme) => {
            localStorage.setItem(key, theme);
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme === 'light' ? 'light' : 'dark';
            updateThemeChecks();
          };

          // Values toggle functionality
          const toggleValues = () => {
            document.body.classList.toggle('show-values');
            const btn = document.getElementById('toggle-values-btn');
            const mobileBtn = document.getElementById('m-toggle-values-mobile');
            const isHidden = document.body.classList.contains('show-values');
            btn.setAttribute('aria-pressed', isHidden);
            btn.querySelector('i').className = isHidden ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
            btn.querySelector('#values-btn-text').textContent = isHidden ? 'Hide values' : 'Show values';
            mobileBtn.querySelector('i').className = isHidden ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
            mobileBtn.querySelector('span').textContent = isHidden ? 'Hide values' : 'Show values';
          };
          document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('toggle-values-btn').addEventListener('click', toggleValues);
            document.getElementById('m-toggle-values-mobile').addEventListener('click', toggleValues);
            document.addEventListener('keydown', (e) => {
              if (e.shiftKey && e.key === 'V') toggleValues();
            });
          });
        } catch(_) {}
      })();
    </script>
    <style>
      /* Prevent transitions during initial theme application */
      html.theme-init *, html.theme-init *::before, html.theme-init *::after {
        transition: none !important;
      }

      /* Sensitive values blur */
      :root {
        --sv-blur: 6px;
        --primary: #2c6b5f;
        --primary-dark: #1f4b43;
        --primary-light: #e6f0ee;
        --text: #1f2937;
        --text-light: #6b7280;
        --card: #ffffff;
        --card-rgb: 255, 255, 255;
        --background: #f9fafb;
        --border: #e5e7eb;
        --shadow-sm: 0 2px 6px rgba(0,0,0,.05);
        --shadow-md: 0 8px 16px rgba(0,0,0,.1);
        --header-h: 68px;
        --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --transition: all .2s cubic-bezier(.3,0,.2,1);
      }

      [data-theme="dark"] {
        --primary: #6fd5c7;
        --primary-dark: #4cb8a9;
        --primary-light: rgba(111, 213, 199, 0.16);
        --text: #e5e7eb;
        --text-light: #9ca3af;
        --card: #111827;
        --card-rgb: 17, 24, 39;
        --background: #0b1220;
        --border: #2d3748;
        --shadow-sm: 0 2px 8px rgba(0,0,0,.5);
        --shadow-md: 0 10px 20px rgba(0,0,0,.6);
        --sv-blur: 5px;
      }

      [data-theme="classic-dark"] {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --primary-light: rgba(59, 130, 246, 0.16);
        --text: #e5e7eb;
        --text-light: #a1a1aa;
        --card: #1f2125;
        --card-rgb: 31, 33, 37;
        --background: #111315;
        --border: #2a2d31;
        --shadow-sm: 0 2px 8px rgba(0,0,0,.55);
        --shadow-md: 0 10px 20px rgba(0,0,0,.65);
        --sv-blur: 5px;
      }

      .sv-blur {
        filter: blur(var(--sv-blur)) saturate(1.1) contrast(.95);
        user-select: none;
        transition: filter .2s ease;
      }

      .show-values .sv-blur {
        filter: none;
      }

      @media (prefers-reduced-motion: reduce) {
        .sv-blur, * { transition: none !important; }
      }

      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      body {
        background: var(--background);
        color: var(--text);
        font-family: var(--font-sans);
        font-size: 16px;
        line-height: 1.5;
        padding-top: calc(var(--header-h) + 1rem);
        overflow-x: hidden;
      }

      .navbar-modern {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        min-height: var(--header-h);
        background: rgba(var(--card-rgb), 0.9);
        backdrop-filter: saturate(200%) blur(12px);
        border-bottom: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        padding: 0.75rem 0;
      }

      [data-theme="dark"] .navbar-modern,
      [data-theme="classic-dark"] .navbar-modern {
        background: rgba(var(--card-rgb), 0.92);
      }

      .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        font-size: 1.5rem;
        letter-spacing: -0.015em;
        color: var(--text);
        text-decoration: none;
        transition: var(--transition);
      }

      .brand-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        background: var(--primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,.15);
        transition: var(--transition);
      }

      .navbar-brand:hover .brand-icon {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0,0,0,.2);
      }

      .nav-link {
        font-weight: 600;
        color: var(--text-light);
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: var(--radius-md);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
      }

      .nav-link i {
        font-size: 1.2rem;
        transition: var(--transition);
      }

      .nav-link:hover {
        color: var(--primary);
        background: var(--primary-light);
        transform: translateY(-2px);
      }

      .nav-link.active {
        color: var(--primary);
        font-weight: 700;
        background: var(--primary-light);
      }

      .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 50%;
        transform: translateX(-50%);
        width: 24px;
        height: 3px;
        background: var(--primary);
        border-radius: 3px;
      }

      .navbar-toggler {
        border: 1px solid var(--border);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        transition: var(--transition);
      }

      .navbar-toggler:focus {
        box-shadow: 0 0 0 3px var(--primary-light);
      }

      .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(44,107,95,0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        transition: var(--transition);
      }

      [data-theme="dark"] .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(111,213,199,0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      }

      [data-theme="classic-dark"] .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(59,130,246,0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      }

      .user-chip {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--card);
        color: var(--text);
        transition: var(--transition);
      }

      .user-chip:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
      }

      .user-chip i {
        font-size: 1.2rem;
      }

      .dropdown-menu {
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        background: var(--card);
        padding: 0.75rem;
        min-width: 220px;
        margin-top: 0.5rem;
      }

      .dropdown-item {
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-sm);
        font-weight: 500;
        color: var(--text);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }

      .dropdown-item:hover,
      .dropdown-item:focus {
        background: var(--primary-light);
        color: var(--primary);
        transform: translateX(4px);
      }

      .dropdown-item.active .check {
        opacity: 1;
      }

      .dropdown-item .check {
        margin-left: auto;
        opacity: 0;
        transition: opacity .2s ease;
      }

      .mobile-menu-backdrop {
        position: fixed;
        top: var(--header-h);
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1029;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
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
          padding: 1.5rem;
          transition: transform 0.3s ease, opacity 0.3s ease;
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
          margin-bottom: 0.75rem;
        }

        .nav-link {
          padding: 1rem 1.5rem;
          font-size: 1.1rem;
        }

        .user-chip-mobile {
          width: 100%;
          justify-content: center;
          margin: 0.75rem 0;
          padding: 0.75rem 1.5rem;
        }
      }

      @media (min-width: 992px) {
        .navbar .container-fluid {
          display: grid;
          grid-template-columns: auto 1fr auto;
          align-items: center;
          gap: 2rem;
        }

        .navbar-nav {
          flex: 1;
          justify-content: center;
          gap: 1.5rem;
        }
      }

      /* Accessibility enhancements */
      .nav-link:focus-visible,
      .user-chip:focus-visible,
      .dropdown-item:focus-visible,
      .navbar-toggler:focus-visible {
        outline: 3px solid var(--primary);
        outline-offset: 3px;
        border-radius: var(--radius-sm);
      }

      /* Scroll-aware navbar */
      .navbar-modern.scrolled {
        box-shadow: var(--shadow-md);
        background: rgba(var(--card-rgb), 0.95);
      }

      /* Values toggle button */
      .values-toggle-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--card);
        color: var(--text);
        transition: var(--transition);
      }

      .values-toggle-btn:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        transform: translateY(-2px);
      }

      /* Reduced motion */
      @media (prefers-reduced-motion: reduce) {
        * { transition: none !important; }
      }
    </style>
</head>
<body>
<div class="mobile-menu-backdrop" id="mobileMenuBackdrop"></div>
<nav class="navbar navbar-expand-lg navbar-light navbar-modern">
  <div class="container-fluid">
    <a class="navbar-brand" href="/home" aria-label="LifeNav Home">
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
          <a class="nav-link<?= $active('home') ?>" href="/home" aria-current="<?= $ctrl === 'home' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('essentials') ?>" href="/essentials" aria-current="<?= $ctrl === 'essentials' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-receipt"></i>
            <span>Spending</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('finance') ?>" href="/finance" aria-current="<?= $ctrl === 'finance' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-sack-dollar"></i>
            <span>Earnings</span>
          </a>
        </li>
        <li class="nav-item d-lg-none">
          <div class="dropdown">
            <button class="btn user-chip user-chip-mobile dropdown-toggle w-100" type="button" 
                    id="userMenuMobile" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-solid fa-user"></i>
              <span class="sv-blur"><?= htmlspecialchars($_SESSION['auth']['name'] ?? ($_SESSION['auth']['email'] ?? '')) ?></span>
            </button>
            <ul class="dropdown-menu w-100" aria-labelledby="userMenuMobile">
              <li><h6 class="dropdown-header">Theme</h6></li>
              <li><button id="m-theme-light" class="dropdown-item" type="button" onclick="setTheme('light')">
                <i class="fa-solid fa-sun"></i>Light <i class="fa-solid fa-check check"></i>
              </button></li>
              <li><button id="m-theme-dark" class="dropdown-item" type="button" onclick="setTheme('dark')">
                <i class="fa-solid fa-moon"></i>Teal Dark <i class="fa-solid fa-check check"></i>
              </button></li>
              <li><button id="m-theme-classic-dark" class="dropdown-item" type="button" onclick="setTheme('classic-dark')">
                <i class="fa-solid fa-moon"></i>Classic Dark <i class="fa-solid fa-check check"></i>
              </button></li>
              <li><hr class="dropdown-divider"></li>
              <li><button id="m-toggle-values-mobile" class="dropdown-item" type="button">
                <i class="fa-solid fa-eye-slash"></i>
                <span>Show values</span>
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
    <div class="d-none d-lg-flex align-items-center" style="gap:.75rem;">
      <button id="toggle-values-btn" type="button" class="values-toggle-btn" aria-pressed="false" aria-label="Show values" title="Show values (Shift+V)">
        <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
        <span id="values-btn-text" class="visually-hidden">Show values</span>
      </button>
      <div class="dropdown">
        <button class="btn user-chip dropdown-toggle" type="button" 
                id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-solid fa-user"></i>
          <span class="sv-blur"><?= htmlspecialchars($_SESSION['auth']['name'] ?? ($_SESSION['auth']['email'] ?? '')) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
          <li><h6 class="dropdown-header">Theme</h6></li>
          <li><button id="theme-light" class="dropdown-item" type="button" onclick="setTheme('light')">
            <i class="fa-solid fa-sun"></i>Light <i class="fa-solid fa-check check"></i>
          </button></li>
          <li><button id="theme-dark" class="dropdown-item" type="button" onclick="setTheme('dark')">
            <i class="fa-solid fa-moon"></i>Teal Dark <i class="fa-solid fa-check check"></i>
          </button></li>
          <li><button id="theme-classic-dark" class="dropdown-item" type="button" onclick="setTheme('classic-dark')">
            <i class="fa-solid fa-moon"></i>Classic Dark <i class="fa-solid fa-check check"></i>
          </button></li>
          <li><hr class="dropdown-divider"></li>
          <li><button id="toggle-values-mobile" class="dropdown-item" type="button">
            <i class="fa-solid fa-eye-slash"></i>
            <span>Show values</span>
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
<script>
  // Scroll-aware navbar effect
  window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar-modern');
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  });

  // Remove theme-init class after DOM load
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      document.documentElement.classList.remove('theme-init');
    }, 100);
  });
</script>
</body>
</html>