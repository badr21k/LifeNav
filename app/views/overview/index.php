<?php require 'app/views/templates/header.php'; ?>

<style>
  .overview-container { padding: 1rem; max-width: 1200px; margin: 0 auto; }
  .ov-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1rem; }
  .ov-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 1rem; }
  .ov-title { font-weight: 700; margin-bottom: .5rem; color: var(--text); }
  .ov-kpi { font-size: 1.5rem; font-weight: 800; color: var(--text); }
  .ov-sub { color: var(--text-light); font-size: .9rem; }
  .ov-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .ov-flex { display: flex; align-items: center; gap: .5rem; }
  .ov-progress { height: .5rem; background: var(--primary-light); border-radius: var(--radius-sm); overflow: hidden; margin-top: .5rem; }
  .ov-progress > div { height: 100%; background: var(--primary); width: 0%; transition: width .3s ease; }
  .chart-card canvas { width: 100%; height: 260px; }
  @media (max-width: 992px){ .ov-row { grid-template-columns: 1fr; } }
  @media (max-width: 640px){ .ov-grid { gap: .75rem; } .chart-card canvas { height: 220px; } }
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
  const fmt = (amt, cur) => new Intl.NumberFormat('en-US', { style: 'currency', currency: cur||'USD' }).format(amt||0);
  const ym = new Date().toISOString().slice(0,7);
  let baseCurrency = 'USD';

  async function getJSON(url){ const r = await fetch(url); if(!r.ok) throw new Error('net'); return r.json(); }

  async function loadSettings(){
    try { const s = await getJSON('/finance/api/settings'); baseCurrency = s?.default_currency || baseCurrency; return s; } catch(_){ return {}; }
  }
  async function loadFinanceSummary(){
    try { return await getJSON(`/finance/api/summary?month=${encodeURIComponent(ym)}`); } catch(_){ return {}; }
  }
  async function loadExpenses(){
    try { return await getJSON('/essentials/api/expenses'); } catch(_){ return []; }
  }
  async function loadPayRuns(){
    try { return await getJSON('/finance/api/payruns'); } catch(_){ return []; }
  }

  function groupBy(arr, key){ return arr.reduce((acc,it)=>{ const k = it[key] || 'Other'; (acc[k]=acc[k]||[]).push(it); return acc; },{}); }

  function calcWeekly(expenses, mode){
    const now = new Date();
    const day = now.getDay(); const diff = now.getDate() - day + (day===0 ? -6:1);
    const start = new Date(now.setDate(diff)); start.setHours(0,0,0,0);
    const end = new Date(start); end.setDate(start.getDate()+6); end.setHours(23,59,59,999);
    const inWeek = expenses.filter(e=> e.mode===mode && e.date >= start.toISOString().slice(0,10) && e.date <= end.toISOString().slice(0,10));
    const toBase = (amt, cur)=> amt * 1; // Assume base for now; server returns currency in base or convert server-side
    const total = inWeek.reduce((s,e)=> s + (e.countWeekly ? toBase(e.amount, e.currency):0), 0);
    return total;
  }

  function calcTotals(expenses, mode){
    const toBase = (amt, cur)=> amt * 1;
    const list = expenses.filter(e=> e.mode===mode);
    return list.reduce((s,e)=> s + toBase(e.amount, e.currency), 0);
  }

  function updateProgress(idFill, idSub, spent, budget){
    const pct = Math.min(100, (budget>0? (spent / budget)*100 : 0));
    const el = document.getElementById(idFill); if (el) el.style.width = pct + '%';
    const sub = document.getElementById(idSub); if (sub) sub.textContent = (isFinite(pct)? pct.toFixed(1):'0.0')+ '% used';
  }

  function setText(id, text){ const el = document.getElementById(id); if(el) el.textContent = text; }

  let chCats, chWeek, chEarn;
  function renderCharts(expenses, payruns){
    const ctxCat = document.getElementById('chart-cats');
    const ctxWeek = document.getElementById('chart-week');
    const ctxEarn = document.getElementById('chart-earnings');

    // Categories
    const cats = groupBy(expenses.filter(e=> e.mode==='normal'), 'category');
    const labelsC = Object.keys(cats);
    const sumsC = labelsC.map(k=> cats[k].reduce((s,e)=> s + (e.amount||0), 0));
    const palette = ['#2c6b5f','#60a5fa','#34d399','#f472b6','#f59e0b','#f97316','#22d3ee','#a78bfa','#ef4444'];
    if (chCats) chCats.destroy();
    chCats = new Chart(ctxCat, { type:'doughnut', data:{ labels: labelsC, datasets:[{ data: sumsC, backgroundColor: palette }] }, options:{ plugins:{ legend:{ display:true, position:'bottom' } } } });

    // Weekly trend (last 7 days)
    const days = [...Array(7)].map((_,i)=>{ const d=new Date(); d.setDate(d.getDate()- (6-i)); return d; });
    const labelDays = days.map(d=> d.toLocaleDateString(undefined,{ month:'short', day:'numeric' }));
    const sumsD = days.map(d=>{
      const iso = d.toISOString().slice(0,10);
      return expenses.filter(e=> e.date===iso && e.mode==='normal' && e.countWeekly).reduce((s,e)=> s + (e.amount||0), 0);
    });
    if (chWeek) chWeek.destroy();
    chWeek = new Chart(ctxWeek, { type:'line', data:{ labels: labelDays, datasets:[{ label:'Spent', data:sumsD, borderColor:'#2c6b5f', backgroundColor:'rgba(44,107,95,0.15)', fill:true, tension:.35, pointRadius:2 }] }, options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ ticks:{ callback:v=> v.toLocaleString() } } } } });

    // Earnings monthly trend (last 6 months)
    const now = new Date();
    const months = [...Array(6)].map((_,i)=>{ const d = new Date(now.getFullYear(), now.getMonth()- (5-i), 1); return d; });
    const labelM = months.map(d=> d.toLocaleDateString(undefined,{ month:'short', year:'2-digit' }));
    const mapMonth = (d)=> d.slice(0,7);
    const sumsM = months.map(d=>{
      const ym = d.toISOString().slice(0,7);
      return payruns.filter(p=> (p.periodStart||'').slice(0,7)===ym).reduce((s,p)=> s + (p.netPay||0), 0);
    });
    if (chEarn) chEarn.destroy();
    chEarn = new Chart(ctxEarn, { type:'bar', data:{ labels: labelM, datasets:[{ label:'Net Pay', data:sumsM, backgroundColor:'#60a5fa' }] }, options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ ticks:{ callback:v=> v.toLocaleString() } } } } });
  }

  async function init(){
    const [settings, summary, expenses, payruns] = await Promise.all([
      loadSettings(), loadFinanceSummary(), loadExpenses(), loadPayRuns()
    ]);
    baseCurrency = settings?.default_currency || baseCurrency;

    // Top KPIs (fallback to 0)
    setText('kpi-earnings', fmt((summary?.income_cents||0)/100, baseCurrency));
    setText('kpi-debt', fmt((summary?.debt_cents||0)/100, baseCurrency));
    setText('kpi-investments', fmt((summary?.investments_cents||0)/100, baseCurrency));
    setText('kpi-savings', fmt((summary?.savings_cents||0)/100, baseCurrency));

    // Mode summaries (derive from Essentials and Settings if present)
    const weeklySpentNormal = calcWeekly(expenses, 'normal');
    const weeklySpentTravel = calcWeekly(expenses, 'travel');
    const totalNormal = calcTotals(expenses, 'normal');
    const totalTravel = calcTotals(expenses, 'travel');

    const weeklyBudgetNormal = (settings?.weekly_budget_normal_cents||0)/100;
    const weeklyBudgetTravel = (settings?.weekly_budget_travel_cents||0)/100;

    setText('nm-total', fmt(totalNormal, baseCurrency));
    setText('tm-total', fmt(totalTravel, baseCurrency));
    setText('nm-weekly-spent', fmt(weeklySpentNormal, baseCurrency));
    setText('tm-weekly-spent', fmt(weeklySpentTravel, baseCurrency));
    setText('nm-weekly-budget', fmt(weeklyBudgetNormal, baseCurrency));
    setText('tm-weekly-budget', fmt(weeklyBudgetTravel, baseCurrency));

    updateProgress('nm-weekly-progress','nm-weekly-sub', weeklySpentNormal, weeklyBudgetNormal||1);
    updateProgress('tm-weekly-progress','tm-weekly-sub', weeklySpentTravel, weeklyBudgetTravel||1);

    renderCharts(expenses, payruns);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
</script>

<?php require 'app/views/templates/footer.php'; ?>
