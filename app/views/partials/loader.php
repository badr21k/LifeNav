<style>
/* Shared Global Loader Styles */
.loading-overlay{position:fixed;inset:0;background:rgba(2,6,12,.18);-webkit-backdrop-filter:blur(6px) saturate(1.1);backdrop-filter:blur(6px) saturate(1.1);display:none;align-items:center;justify-content:center;z-index:2000}
.loading-overlay.active{display:flex}
.loader-card{display:flex;align-items:center;gap:.875rem;padding:.875rem 1rem;background:var(--card,#fff);color:var(--text,#111);border:1px solid var(--border,#ddd);border-radius:.75rem;box-shadow:0 4px 12px rgba(0,0,0,.08)}
.loader-text{font-weight:600;letter-spacing:-.01em}
.loader-spinner{inline-size:28px;block-size:28px;border-radius:50%;background:conic-gradient(from 0deg,var(--primary,#2c6b5f) 0 340deg,transparent 340deg 360deg);-webkit-mask:radial-gradient(farthest-side,transparent 60%,#000 61%);mask:radial-gradient(farthest-side,transparent 60%,#000 61%);animation:loader-spin .9s linear infinite;box-shadow:0 0 0 1px rgba(0,0,0,.04) inset}
@keyframes loader-spin{to{transform:rotate(1turn)}}
</style>
<script>
(function(){
  const overlay = document.createElement('div');
  overlay.className = 'loading-overlay';
  overlay.id = 'global-loader-overlay';
  overlay.innerHTML = '<div class="loader-card" role="status" aria-live="polite"><div class="loader-spinner" aria-hidden="true"></div><div class="loader-text">Loading...</div></div>';
  document.addEventListener('DOMContentLoaded', function(){
    if(!document.getElementById('global-loader-overlay')) document.body.appendChild(overlay);
  });
  const Loader = {
    _count: 0,
    show(){ this._count++; const el=document.getElementById('global-loader-overlay'); if(el) el.classList.add('active'); },
    hide(){ this._count=Math.max(0,this._count-1); if(this._count===0){ const el=document.getElementById('global-loader-overlay'); if(el) el.classList.remove('active'); } },
    reset(){ this._count=0; const el=document.getElementById('global-loader-overlay'); if(el) el.classList.remove('active'); }
  };
  window.GlobalLoader = Loader;
})();
</script>
