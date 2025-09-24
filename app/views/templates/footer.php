<footer class="footer">    
    <div class="row">
        <div class="col-lg-12">
            <p>Copyright &copy; <?php echo date('Y'); ?> </p>
        </div>
    </div>
</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
  (function(){
    // Global theme init and toggle
    var THEME_KEY = 'lifenav_theme';
    try {
      var saved = localStorage.getItem(THEME_KEY);
      if (!saved) {
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        saved = prefersDark ? 'dark' : 'light';
        localStorage.setItem(THEME_KEY, saved);
      }
      if (saved === 'dark') { document.documentElement.setAttribute('data-theme','dark'); }
      else { document.documentElement.removeAttribute('data-theme'); }
    } catch(_) {}
    window.toggleTheme = function(){
      try {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var next = isDark ? 'light' : 'dark';
        if (next === 'dark') document.documentElement.setAttribute('data-theme','dark');
        else document.documentElement.removeAttribute('data-theme');
        localStorage.setItem(THEME_KEY, next);
      } catch(_){}
    };
    document.addEventListener('DOMContentLoaded', function(){
      var navEl = document.getElementById('navbarSupportedContent');
      var toggler = document.querySelector('.navbar-toggler');
      var header = document.querySelector('.navbar-modern');
      if (header){
        var onScroll = function(){ header.classList.toggle('scrolled', window.scrollY > 2); };
        onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
      }
      if (!navEl) return;
      var bsCollapse = null;
      try { bsCollapse = bootstrap.Collapse.getOrCreateInstance(navEl, { toggle:false }); } catch(_) {}

      function closeMenu(){ try{ bsCollapse && bsCollapse.hide(); }catch(_){} navEl.classList.remove('show'); if (toggler){ toggler.setAttribute('aria-expanded','false'); toggler.classList.add('collapsed'); } }

      // Close on nav-link click
      navEl.addEventListener('click', function(e){
        var a = e.target.closest('a.nav-link');
        if (!a) return;
        if (window.innerWidth < 992 && navEl.classList.contains('show')){
          e.preventDefault();
          var href = a.getAttribute('href');
          closeMenu();
          setTimeout(function(){ if (href) window.location.href = href; }, 150);
        } else {
          closeMenu();
        }
      });
      // Close on outside click
      document.addEventListener('click', function(e){ if (navEl.classList.contains('show') && !e.target.closest('.navbar')) closeMenu(); });
      // Close on Escape
      document.addEventListener('keydown', function(e){ if (e.key==='Escape' && navEl.classList.contains('show')) closeMenu(); });
      // Close on resize to desktop
      window.addEventListener('resize', function(){ if (window.innerWidth>=992 && navEl.classList.contains('show')) closeMenu(); });

      // Dropdowns: close after item click on mobile
      document.querySelectorAll('.dropdown').forEach(function(dd){
        var toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
        var menu = dd.querySelector('.dropdown-menu');
        if (!toggle || !menu) return;
        var d = null; try { d = bootstrap.Dropdown.getOrCreateInstance(toggle); } catch(_){ }
        menu.addEventListener('click', function(e){
          if (e.target.closest('.dropdown-item')){
            try { d && d.hide(); } catch(_){ }
            if (window.innerWidth < 992 && navEl.classList.contains('show')) closeMenu();
          }
        });
      });
    });
  })();
</script>

</body>
</html>