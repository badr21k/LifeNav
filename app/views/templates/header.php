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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
      :root{
        --primary:#2c6b5f; --primary-dark:#1f4b43; --primary-light:#e6f0ee;
        --text:#111827; --text-light:#6b7280; --card:#ffffff; --background:#f8fafc; --border:#e5e7eb;
        --shadow-sm:0 2px 4px rgba(0,0,0,.06); --shadow-md:0 10px 20px rgba(2,6,12,.08);
        --header-h:64px;
        --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        --radius-sm:.5rem; --radius-md:.75rem; --radius-lg:1rem;
        --transition: all .25s cubic-bezier(.4,0,.2,1);
      }
      [data-theme="dark"]{
        --primary:#4ca89b; --primary-dark:#3b867b; --primary-light:#1a3c34;
        --text:#f3f4f6; --text-light:#d1d5db; --card:#0f172a; --background:#0b1220; --border:#1f2a44;
        --shadow-sm:0 2px 4px rgba(0,0,0,.4); --shadow-md:0 10px 20px rgba(0,0,0,.5);
      }
      .navbar.navbar-modern{ position: sticky; top:0; z-index: 3000; min-height: var(--header-h); display:flex; align-items:center; backdrop-filter:saturate(1.2) blur(10px); background: var(--card) !important; border-bottom:1px solid var(--border); box-shadow: var(--shadow-sm); }
      [data-theme="dark"] .navbar.navbar-modern{ background: var(--card) !important; box-shadow: var(--shadow-md); }
      .navbar .navbar-brand{ display:flex; align-items:center; gap:.6rem; font-weight:900; letter-spacing:-.02em; color:var(--text); }
      .navbar .brand-icon{ width:32px; height:32px; border-radius:10px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; box-shadow:0 2px 8px rgba(44,107,95,.25); }
      .navbar .nav-link{ font-weight:800; color:var(--text-light); padding:.7rem 1.1rem; font-size:1rem; }
      .navbar .nav-link i{ margin-right:.45rem; opacity:.9; }
      .navbar .nav-link.active{ color:var(--primary); background:transparent; box-shadow:none; }
      .navbar .nav-link:hover{ color:var(--primary); background:transparent; }
      .navbar .navbar-collapse{ display:flex; align-items:center; }
      .navbar .navbar-nav{ flex:1 1 auto; justify-content:center; gap:1.25rem; }
      .navbar .navbar-text{ color:var(--text); font-weight:700; }
      .user-chip{ display:flex; align-items:center; gap:.5rem; font-weight:700; }
      .user-chip i{ opacity:.9; }
      .dropdown-menu{ z-index: 4000; box-shadow: var(--shadow-md); border:1px solid var(--border); background:var(--card); }
      .navbar .collapse{ overflow: visible; }
      body{ background:var(--background); color:var(--text); font-family: var(--font-sans); padding-top: var(--header-h); }
      a{ color: var(--text); text-decoration: none; transition: var(--transition); }
      a:hover{ color: var(--primary); }
      label, .form-label, th{ color: var(--text); }
      .dropdown-item{ color: var(--text); }
      .dropdown-item:hover, .dropdown-item:focus{ background: var(--primary-light); color: var(--text); }
      .btn, .btn-outline-secondary{ color: var(--text); border-color: var(--border); }
      .btn:hover, .btn:focus{ filter: brightness(0.98); }
      .footer{ background:transparent; color:var(--text-light); border-top:1px solid var(--border); padding:1rem; text-align:center; }
      /* Precise centering for header tabs on large screens */
      @media (min-width: 992px) {
        .navbar .container-fluid { display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 1rem; }
        .navbar .navbar-collapse { grid-column: 2; justify-content: center !important; }
        .navbar .d-lg-flex { grid-column: 3; }
        .navbar .navbar-nav { margin: 0 auto; }
      }
    </style>
    <script>
      (function(){
        const key='lifenav_theme';
        try {
          let saved = localStorage.getItem(key);
          if (!saved) {
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            saved = prefersDark ? 'dark' : 'light';
            localStorage.setItem(key, saved);
          }
          if (saved === 'dark') document.documentElement.setAttribute('data-theme','dark');
        } catch(_) {}
        window.toggleTheme = function(){
          const cur = document.documentElement.getAttribute('data-theme')==='dark' ? 'light' : 'dark';
          if(cur==='light') document.documentElement.removeAttribute('data-theme'); else document.documentElement.setAttribute('data-theme','dark');
          try { localStorage.setItem(key, cur); } catch(_) {}
        }
        // Mobile navbar: auto-collapse on link click or outside tap
        document.addEventListener('DOMContentLoaded', function(){
          var navEl = document.getElementById('navbarSupportedContent');
          var toggler = document.querySelector('.navbar-toggler');
          if (!navEl) return;
          // Use Bootstrap Collapse API if available
          var bsCollapse = null;
          try { bsCollapse = (window.bootstrap && bootstrap.Collapse) ? new bootstrap.Collapse(navEl, { toggle: false }) : null; } catch(_) {}

          function closeMenu(){
            try { if (bsCollapse) { bsCollapse.hide(); return; } } catch(_) {}
            // Fallback if Bootstrap isn't ready: manually collapse
            navEl.classList.remove('show');
            if (toggler){ toggler.setAttribute('aria-expanded','false'); toggler.classList.add('collapsed'); }
          }

          // Close on nav link tap
          navEl.addEventListener('click', function(e){
            var a = e.target.closest('a.nav-link');
            if (a) closeMenu();
          });
          // Close when clicking outside
          document.addEventListener('click', function(e){
            if (!navEl.classList.contains('show')) return;
            if (!e.target.closest('.navbar')) closeMenu();
          });
          // Close on Escape anywhere when menu is open
          document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && navEl.classList.contains('show')) closeMenu();
          });
          // Reset collapsed state on resize to avoid stuck aria states
          window.addEventListener('resize', function(){
            if (window.innerWidth >= 992) { navEl.classList.remove('show'); if (toggler){ toggler.setAttribute('aria-expanded','false'); toggler.classList.add('collapsed'); } }
          });

          // Dropdown accessibility and close behavior for user menus (desktop + mobile)
          function wireDropdown(triggerId, menuId){
            var trigger = document.getElementById(triggerId);
            var menu = document.getElementById(menuId);
            if (!trigger || !menu) return;
            var bsDrop = null; try { bsDrop = (window.bootstrap && bootstrap.Dropdown) ? new bootstrap.Dropdown(trigger, { autoClose: true }) : null; } catch(_) {}

            function focusFirstItem(){
              var first = menu.querySelector('.dropdown-item, a[role="menuitem"], button[role="menuitem"]');
              if (first) first.focus({ preventScroll: true });
            }
            trigger.addEventListener('shown.bs.dropdown', function(){
              trigger.setAttribute('aria-expanded','true');
              focusFirstItem();
            });
            trigger.addEventListener('hidden.bs.dropdown', function(){
              trigger.setAttribute('aria-expanded','false');
              trigger.focus({ preventScroll: true });
            });
            // Close on escape manually (Bootstrap handles, but ensure)
            menu.addEventListener('keydown', function(e){ if(e.key==='Escape'){ try{ bsDrop && bsDrop.hide(); }catch(_){} } });
            // Close after selecting an item and also close the collapsed navbar (mobile)
            menu.addEventListener('click', function(e){
              if(e.target.closest('.dropdown-item')){
                try{ bsDrop && bsDrop.hide(); }catch(_){}
                closeMenu();
              }
            });
          }
          wireDropdown('userMenu','userMenuMenu');
          wireDropdown('userMenuMobile','userMenuMobileMenu');
        });
      })();
    </script>
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
      <!-- Centered Nav -->
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?= $active('home') ?>" href="/home"><i class="fa-solid fa-house"></i> Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('essentials') ?>" href="/essentials"><i class="fa-solid fa-receipt"></i> Spending</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $active('finance') ?>" href="/finance"><i class="fa-solid fa-sack-dollar"></i> Earnings</a>
        </li>
        
        <!-- Mobile user menu inside collapse -->
        <li class="nav-item dropdown d-lg-none mt-2">
          <button class="btn btn-outline-secondary btn-sm dropdown-toggle user-chip" type="button" id="userMenuMobile" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="userMenuMobileMenu">
            <i class="fa-solid fa-user"></i>
            <?= htmlspecialchars($_SESSION['auth']['name'] ?? ($_SESSION['auth']['email'] ?? '')) ?>
          </button>
          <ul id="userMenuMobileMenu" class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuMobile" role="menu">
            <li><button class="dropdown-item" type="button" role="menuitem" onclick="toggleTheme()"><i class="fa-solid fa-moon me-2"></i>Toggle Dark Mode</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/logout" role="menuitem"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
          </ul>
        </li>
    </div>

    <!-- Right (User menu) -->
    <div class="d-none d-lg-flex align-items-center justify-content-end">
      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle user-chip" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="userMenuMenu">
          <i class="fa-solid fa-user"></i>
          <?= htmlspecialchars($_SESSION['auth']['name'] ?? ($_SESSION['auth']['email'] ?? '')) ?>
        </button>
        <ul id="userMenuMenu" class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu" role="menu">
          <li><button class="dropdown-item" type="button" onclick="toggleTheme()"><i class="fa-solid fa-moon me-2"></i>Toggle Dark Mode</button></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="/logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
