<?php require 'app/views/templates/header.php'; ?>

<style>
  :root { --bg:#0c1412; --card:#0f1a17; --text:#e6f1ee; --muted:#a7c1bb; --chip-bg:#0c1f19; --primary:#2c6b5f; --border:#17312a; }
  .container{ max-width:1200px; margin:0 auto; padding:16px; }
  .grid{ display:grid; grid-template-columns: repeat(12, 1fr); gap:14px; }
  .ov-card{ background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px; box-shadow: 0 8px 24px rgba(0,0,0,.24); }
  .ov-title{ color:var(--muted); font-weight:700; margin-bottom:10px; letter-spacing:.2px; text-transform:none; }
  .ov-kpi{ font-size:1.35rem; font-weight:800; color:var(--text); }
  .ov-sub{ color:var(--muted); font-size:.85rem; }
  .ov-flex{ display:flex; justify-content:space-between; align-items:center; color:var(--text); gap:8px; }
  .ov-progress{ height:8px; border-radius:999px; background:#153830; overflow:hidden; margin-top:8px; }
  .ov-progress>div{ height:100%; width:0%; background:var(--primary); transition:width .35s ease; }
  .kpi-row{ display:flex; gap:12px; align-items:center; color:var(--muted); font-size:.85rem; }
  .kpi-row strong{ color:var(--text); }
  canvas{ width:100%; height:260px; }
  @media (max-width: 900px){ .grid{ grid-template-columns: repeat(6,1fr); } }
  @media (max-width: 640px){ .grid{ grid-template-columns: repeat(2,1fr); } canvas{ height:220px; } }
</style>

<div class="overview-container">
  <div class="ov-grid">
    <!-- Top KPIs -->
    <div class="ov-card" style="grid-column: span 3;">
      <div class="ov-title">Earnings YTD</div>
      <div id="kpi-earnings" class="ov-kpi sensitive-value">$0.00</div>
      <div class="ov-sub">Year-to-date</div>
    </div>
    <div class="ov-card" style="grid-column: span 3;">
      <div class="ov-title">Total Debt</div>
      <div id="kpi-debt" class="ov-kpi sensitive-value">$0.00</div>
      <div class="ov-sub">Across all accounts</div>
    </div>
    <div class="ov-card" style="grid-column: span 3;">
      <div class="ov-title">Investments Value</div>
      <div id="kpi-investments" class="ov-kpi sensitive-value">$0.00</div>
      <div class="ov-sub">Portfolio total</div>
    </div>
    <div class="ov-card" style="grid-column: span 3;">
      <div class="ov-title">Savings Progress</div>
      <div id="kpi-savings" class="ov-kpi sensitive-value">$0.00</div>
      <div class="ov-sub">Saved vs targets</div>
    </div>

    <!-- Mode summaries -->
    <div class="ov-card" style="grid-column: span 6;">
      <div class="ov-title">Summary — Normal Mode</div>
      <div class="container">
        <div style="display:flex; gap:12px; align-items:center; justify-content:flex-end; margin-bottom:12px;">
          <div>
            <label for="ov-month" style="font-size:.85rem; color:var(--muted); display:block;">Month</label>
            <input type="month" id="ov-month" style="padding:.35rem .5rem; border:1px solid var(--border); border-radius:6px; background:var(--card); color:var(--text);">
          </div>
          <div>
            <label style="font-size:.85rem; color:var(--muted); display:block;">Status</label>
            <span id="ov-status" class="badge" style="display:inline-block; padding:.25rem .5rem; border-radius:999px; background:var(--chip-bg); color:var(--muted);">—</span>
          </div>
        </div>
        <div class="ov-flex">
          <span>Total</span>
          <strong id="nm-total" class="sensitive-value">$0.00</strong>
        </div>
        <div class="ov-flex" style="margin-top:.5rem;">
          <span>Weekly Spent / Budget</span>
          <strong>
            <span id="nm-weekly-spent" class="sensitive-value">$0.00</span>
            <span> / </span>
            <span id="nm-weekly-budget" class="sv-exempt">$0.00</span>
          </strong>
        </div>
        <div class="ov-progress"><div id="nm-weekly-progress"></div></div>
        <div class="ov-sub" id="nm-weekly-sub">0% used</div>
      </div>
          <span id="nm-weekly-spent" class="sensitive-value">$0.00</span>
          <span> / </span>
          <span id="nm-weekly-budget" class="sv-exempt">$0.00</span>
        </strong>
      </div>
      <div class="ov-progress"><div id="nm-weekly-progress"></div></div>
      <div class="ov-sub" id="nm-weekly-sub">0% used</div>
    </div>
    <div class="ov-card" style="grid-column: span 6;">
      <div class="ov-title">Summary — Travel Mode</div>
      <div class="ov-flex">
        <span>Total</span>
        <strong id="tm-total" class="sensitive-value">$0.00</strong>
      </div>
      <div class="ov-flex" style="margin-top:.5rem;">
        <span>Weekly Spent / Budget</span>
        <strong>
          <span id="tm-weekly-spent" class="sensitive-value">$0.00</span>
          <span> / </span>
          <span id="tm-weekly-budget" class="sv-exempt">$0.00</span>
        </strong>
      </div>
      <div class="ov-progress"><div id="tm-weekly-progress"></div></div>
      <div class="ov-sub" id="tm-weekly-sub">0% used</div>
    </div>

    <!-- Charts -->
    <div class="ov-card chart-card" style="grid-column: span 6;">
      <div class="ov-title">Spending by Category</div>
      <canvas id="chart-cats"></canvas>
    </div>
    <div class="ov-card chart-card" style="grid-column: span 6;">
      <div class="ov-title">Weekly Spending Trend</div>
      <canvas id="chart-week"></canvas>
    </div>
    <div class="ov-card chart-card" style="grid-column: span 12;">
      <div class="ov-title">Monthly Earnings Trend</div>
      <canvas id="chart-earnings"></canvas>
    </div>
  </div>
</div>

<!-- Chart.js for this page only -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  let currentMonth = (localStorage.getItem('overviewMonth') || new Date().toISOString().slice(0,7));
  const fmt = (amt, cur) => new Intl.NumberFormat('en-US', { style: 'currency', currency: cur||'CAD' }).format(amt||0);
  const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';

  async function getJSON(url){ const r=await fetch(url,{credentials:'same-origin'}); if(!r.ok) throw new Error('net'); return r.json(); }
  function setText(id, text){ const el=document.getElementById(id); if(el) el.textContent = text; }
  function updateProgress(idFill, idSub, spent, budget){ const pct=Math.min(100, budget>0? (spent/budget)*100 : 0); const f=document.getElementById(idFill); if(f) f.style.width=pct+'%'; const s=document.getElementById(idSub); if(s) s.textContent=`${isFinite(pct)?pct.toFixed(1):'0.0'}% used / ${fmt(budget)}`; }

  let chCats, chWeek, chEarn;
  function injectTravelToggle(){
    try{
      const host = document.getElementById('chart-cats')?.parentElement;
      if (!host || document.getElementById('toggle-travel-visible')) return;
      const wrap = document.createElement('div');
      wrap.style.display='flex'; wrap.style.justifyContent='flex-end'; wrap.style.marginBottom='6px';
      wrap.innerHTML = `<label id="toggle-travel-visible" style="font-size:.875rem; display:flex; align-items:center; gap:.5rem; cursor:pointer">
        <input type="checkbox" id="ck-travel" /> Show Travel Categories
      </label>`;
      host.parentElement.insertBefore(wrap, host);
      const ck = wrap.querySelector('#ck-travel');
      if (localStorage.getItem('overviewShowTravel') === null) localStorage.setItem('overviewShowTravel','1');
      const pref = localStorage.getItem('overviewShowTravel') === '1';
      ck.checked = pref;
      ck.addEventListener('change', ()=>{ localStorage.setItem('overviewShowTravel', ck.checked?'1':'0'); try{ if (renderCharts.__last) renderCharts(...renderCharts.__last); }catch(_){} });
    }catch(_e){}
  }

  async function renderCharts(catsNormal, catsTravel, kpis, currency){
    renderCharts.__last = [catsNormal, catsTravel, kpis, currency];
    const ctxCat = document.getElementById('chart-cats');
    const ctxWeek = document.getElementById('chart-week');
    const ctxEarn = document.getElementById('chart-earnings');
    const palette = ['#2c6b5f','#60a5fa','#34d399','#f472b6','#f59e0b','#f97316','#22d3ee','#a78bfa','#ef4444'];

    // Categories doughnut (Normal + optional Travel overlay)
    const labelsC = Object.keys(catsNormal||{});
    const sumsC = labelsC.map(k=> catsNormal[k]||0);
    const showTravel = localStorage.getItem('overviewShowTravel') === '1';
    const travelAligned = labelsC.map(k => (catsTravel||{})[k]||0);
    if (chCats) chCats.destroy();
    const datasets=[{ data: sumsC, backgroundColor: palette, label:'Normal' }];
    if (showTravel) datasets.push({ data: travelAligned, backgroundColor: palette.map(c=>c+'99'), label:'Travel' });
    chCats = new Chart(ctxCat, { type:'doughnut', data:{ labels: labelsC, datasets }, options:{ plugins:{ legend:{ display:true, position:'bottom' } } } });

    // Weekly Spending Trend (if kpis can supply; fallback to static zeros)
    const days = [...Array(7)].map((_,i)=>{ const d=new Date(); d.setDate(d.getDate()-(6-i)); return d; });
    const labelDays = days.map(d=> d.toLocaleDateString(undefined,{ month:'short', day:'numeric' }));
    const sumsD = Array.isArray(kpis?.week_series) && kpis.week_series.length===7 ? kpis.week_series : new Array(7).fill(0);
    if (chWeek) chWeek.destroy();
    chWeek = new Chart(ctxWeek, { type:'line', data:{ labels: labelDays, datasets:[{ label:'Spent', data:sumsD, borderColor:'#2c6b5f', backgroundColor:'rgba(44,107,95,0.15)', fill:true, tension:.35, pointRadius:2 }] }, options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ ticks:{ callback:v=> v.toLocaleString() } } } } });

    // Earnings monthly trend — optional extension; render empty for now
    // Monthly Earnings Trend: fetch series from server (from first input month to today)
    try {
      const s = await getJSON('/overview_api/series');
      const labels = (s?.data?.labels)||[];
      const series = (s?.data?.paycheck)||[];
      if (chEarn) chEarn.destroy();
      chEarn = new Chart(ctxEarn, { type:'bar', data:{ labels, datasets:[{ label:'Net Pay', data:series, backgroundColor:'#60a5fa' }] }, options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ ticks:{ callback:v=> v.toLocaleString() } } } } });
    } catch(_e) {
      const months = [...Array(6)].map((_,i)=>{ const d=new Date(); d.setMonth(d.getMonth()-(5-i)); return d; });
      const labelM = months.map(d=> d.toLocaleDateString(undefined,{ month:'short', year:'2-digit' }));
      const sumsM = new Array(6).fill(0);
      if (chEarn) chEarn.destroy();
      chEarn = new Chart(ctxEarn, { type:'bar', data:{ labels: labelM, datasets:[{ label:'Net Pay', data:sumsM, backgroundColor:'#60a5fa' }] }, options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ ticks:{ callback:v=> v.toLocaleString() } } } } });
    }
  }

  async function init(){
    try {
      const month = currentMonth;
      const monthInput = document.getElementById('ov-month'); if (monthInput) monthInput.value = month;
      try { await fetch('/overview_api/save', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN }, body: JSON.stringify({ month }) }); } catch(_e){}
      let res = null; let data = {};
      try { res = await getJSON(`/overview_api/get/${encodeURIComponent(month)}`); data = res?.data || {}; } catch(_e){ data = {}; }
      // Fallback if totals are missing or paycheck is 0 — pull from Finance summary directly
      if (!data.totals || (typeof data.totals.paycheck_month !== 'number')) {
        try {
          const f = await getJSON(`/finance/api/summary?month=${encodeURIComponent(month)}`);
          data.totals = {
            income_month: (f?.income_cents||0)/100,
            spending_month: (f?.expenses_cents||0)/100,
            net_month: ((f?.income_cents||0)-(f?.expenses_cents||0))/100,
            paycheck_month: (f?.paycheck_cents||0)/100
          };
          data.kpis = data.kpis || {};
          setText('ov-status','Live');
        } catch(_e){}
      } else {
        setText('ov-status','Cached');
      }
      const currency = data.currency || 'CAD';

      const t = data.totals || {};
      setText('kpi-earnings', fmt(t.income_month||0, currency));
      // Note: debt/investments/savings snapshots can be added to kpis_json later
      setText('kpi-debt', fmt((data.kpis?.debts_total)||0, currency));
      setText('kpi-investments', fmt((data.kpis?.investments_total)||0, currency));
      setText('kpi-savings', fmt((data.kpis?.savings_total)||0, currency));

      // Mode summaries
      const k = data.kpis || {};
      setText('nm-total', fmt((k.total_normal)||0, currency));
      setText('tm-total', fmt((k.total_travel)||0, currency));

      const spentWeek = k.spent_this_week || 0;
      const wb = k.weekly_budget || 0;
      setText('nm-weekly-spent', fmt(spentWeek, currency));
      setText('tm-weekly-spent', fmt((k.spent_this_week_travel)||0, currency));
      setText('nm-weekly-budget', fmt(wb, currency));
      setText('tm-weekly-budget', fmt((k.weekly_budget_travel)||0, currency));
      updateProgress('nm-weekly-progress','nm-weekly-sub', spentWeek, wb||1);
      updateProgress('tm-weekly-progress','tm-weekly-sub', (k.spent_this_week_travel)||0, (k.weekly_budget_travel)||1);

      // Charts
      injectTravelToggle();
      await renderCharts(data.categories_normal || {}, data.categories_travel || {}, k, currency);
    } catch (e) {
      console.warn('overview load failed', e);
      try { const t=document.createElement('div'); t.className='toast error'; t.textContent='Overview failed to load'; document.body.appendChild(t); setTimeout(()=>t.remove(),3000);}catch(_){ }
    }
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    const monthInput = document.getElementById('ov-month');
    if (monthInput) {
      monthInput.value = currentMonth;
      monthInput.addEventListener('change', ()=>{
        const v = monthInput.value || new Date().toISOString().slice(0,7);
        currentMonth = v; localStorage.setItem('overviewMonth', v);
        init();
      });
    }
    init();
  });
  // Live updates when Finance posts a pay_update for this month
  try {
    const ch = new BroadcastChannel('lifenav_finance');
    ch.onmessage = (ev)=>{ if (ev?.data?.type==='pay_update' && ev?.data?.month===currentMonth) { init(); } };
  } catch(_e){}
  try {
    const ch2 = new BroadcastChannel('lifenav_spending');
    ch2.onmessage = (ev)=>{ if (ev?.data?.type==='spend_update' && ev?.data?.month===currentMonth) { init(); } };
  } catch(_e){}
})();
</script>

<?php require 'app/views/templates/footer.php'; ?>
