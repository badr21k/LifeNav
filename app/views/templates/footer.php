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
    function applyTheme(theme){
      if (theme === 'dark' || theme === 'classic-dark') {
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.style.colorScheme = 'dark';
      } else {
        document.documentElement.removeAttribute('data-theme');
        document.documentElement.style.colorScheme = 'light';
      }
      localStorage.setItem(THEME_KEY, theme);
      try { window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } })); } catch(_) {}
    }
    window.setTheme = function(theme){
      try { applyTheme(theme); } catch(_) {}
    };
    window.toggleTheme = function(){
      try {
        var cur = localStorage.getItem(THEME_KEY) || (document.documentElement.getAttribute('data-theme') || 'light');
        var order = ['light','dark','classic-dark'];
        var idx = order.indexOf(cur);
        var next = order[(idx+1) % order.length];
        applyTheme(next);
      } catch(_){}
    }
    // Global chart palette helper using current CSS variables
    window.LifeNavGetChartPalette = function(){
      var cs = getComputedStyle(document.documentElement);
      function v(n){ return cs.getPropertyValue(n).trim() || '#888'; }
      var primary = v('--primary');
      var text = v('--text');
      var textLight = v('--text-light');
      var border = v('--border');
      var alt = 'rgba(111,213,199,0.35)';
      return [primary, text, textLight, border, alt];
    };
    // Auto-update any charts registered on theme change
    window.addEventListener('themechange', function(){
      try {
        var palette = window.LifeNavGetChartPalette();
        (window.LifeNavCharts || []).forEach(function(ch){
          try {
            if (ch && ch.data && ch.data.datasets && ch.data.datasets[0]){
              ch.data.datasets.forEach(function(ds){ ds.backgroundColor = palette; ds.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--card').trim() || '#fff'; });
              ch.update('none');
            }
          } catch(_) {}
        });
      } catch(_) {}
    });
    document.addEventListener('DOMContentLoaded', function(){
      try { document.documentElement.classList.remove('theme-init'); } catch(_) {}
      // Initialize amounts visibility from storage (default hidden)
      var SHOW_KEY = 'lifenav_showAmounts';
      // migrate from legacy key if present
      try {
        if (localStorage.getItem(SHOW_KEY) === null) {
          var legacy = localStorage.getItem('lifenav_show_values');
          if (legacy !== null) localStorage.setItem(SHOW_KEY, legacy);
        }
      } catch(_) {}
      var showValues = false;
      try { showValues = localStorage.getItem(SHOW_KEY) === 'true'; } catch(_) {}
      function maskElement(el){
        if (el.classList.contains('sv-exempt') || el.hasAttribute('data-sv-exempt')) return;
        if (!el.dataset.origText) el.dataset.origText = el.textContent;
        el.textContent = '$X.XX';
      }
      function unmaskElement(el){
        if (el.classList.contains('sv-exempt') || el.hasAttribute('data-sv-exempt')) return;
        if (el.dataset && el.dataset.origText !== undefined) el.textContent = el.dataset.origText;
      }
      function applyShowValues(flag){
        // flag === true => show actual amounts; false => show literal placeholder
        try { localStorage.setItem(SHOW_KEY, flag ? 'true' : 'false'); } catch(_) {}
        try {
          document.querySelectorAll('.sensitive-value').forEach(function(el){
            if (flag) unmaskElement(el); else maskElement(el);
          });
        } catch(_) {}
        // Update header button states
        var btn = document.getElementById('toggle-values-btn');
        if (btn){
          btn.setAttribute('aria-pressed', String(flag));
          btn.setAttribute('aria-label', flag ? 'Hide amounts' : 'Show amounts');
          var ic = btn.querySelector('i');
          if (ic) ic.className = flag ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
          btn.title = flag ? 'Hide amounts' : 'Show amounts';
        }
        // Update mobile items text/icon
        var items = [document.getElementById('toggle-values-mobile'), document.getElementById('m-toggle-values-mobile')];
        items.forEach(function(el){
          if (!el) return;
          var i = el.querySelector('i'); var s = el.querySelector('span');
          if (i) i.className = flag ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
          if (s) s.textContent = flag ? 'Hide amounts' : 'Show amounts';
        });
      }
      applyShowValues(showValues);
      var toggleBtn = document.getElementById('toggle-values-btn');
      if (toggleBtn){
        toggleBtn.addEventListener('click', function(){ showValues = !showValues; applyShowValues(showValues); });
        toggleBtn.addEventListener('keydown', function(e){ if (e.key==='Enter' || e.key===' '){ e.preventDefault(); showValues = !showValues; applyShowValues(showValues); }});
      }
      // Mobile dropdown items
      ['toggle-values-mobile','m-toggle-values-mobile'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.addEventListener('click', function(){ showValues = !showValues; applyShowValues(showValues); });
      });
      // Keyboard shortcut Shift+V
      document.addEventListener('keydown', function(e){
        if ((e.shiftKey && (e.key==='v' || e.key==='V')) && !e.defaultPrevented) {
          showValues = !showValues; applyShowValues(showValues);
        }
      });

      // Mark numeric text nodes as sensitive automatically when inside elements marked via data-sv or common classes
      function markSensitive(root){
        var selectors = ['.sensitive-value','[data-sensitive]','[data-sv]','.summary-value','.stat-value','#income-value','#expenses-value'];
        root.querySelectorAll(selectors.join(',')).forEach(function(el){
          el.classList.add('sensitive-value');
          // Apply current state
          if (showValues) unmaskElement(el); else maskElement(el);
        });
      }
      markSensitive(document);
      // Observe dynamic content changes (React renders)
      try {
        var obs = new MutationObserver(function(muts){
          muts.forEach(function(m){
            if (m.type==='childList') {
              markSensitive(m.target);
              // Apply current mask state to new nodes
              var current = localStorage.getItem(SHOW_KEY) === 'true';
              document.querySelectorAll('.sensitive-value').forEach(function(el){ if (current) unmaskElement(el); else maskElement(el); });
            }
          });
        });
        obs.observe(document.body, { childList: true, subtree: true });
      } catch(_) {}

      // Theme checkmarks
      function updateThemeChecks(theme){
        var ids = ['theme-light','theme-dark','theme-classic-dark','m-theme-light','m-theme-dark','m-theme-classic-dark'];
        ids.forEach(function(id){ var el = document.getElementById(id); if (!el) return; el.classList.remove('active'); });
        var map = { 'light': ['theme-light','m-theme-light'], 'dark': ['theme-dark','m-theme-dark'], 'classic-dark': ['theme-classic-dark','m-theme-classic-dark'] };
        (map[theme]||[]).forEach(function(id){ var el = document.getElementById(id); if (el) el.classList.add('active'); });
      }
      try { var currentTheme = localStorage.getItem('lifenav_theme') || (document.documentElement.getAttribute('data-theme') || 'light'); updateThemeChecks(currentTheme); } catch(_) {}
      window.addEventListener('themechange', function(ev){ try { updateThemeChecks(ev.detail && ev.detail.theme || 'light'); } catch(_) {} });
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