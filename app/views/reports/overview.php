<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3">
  <h3><?= htmlspecialchars($title) ?></h3>

  <form class="row g-2" method="get" action="/reports/overview">
    <div class="col-auto">
      <select class="form-select" name="mode">
        <option value="normal" <?= ($_GET['mode'] ?? 'normal')==='normal'?'selected':'' ?>>Normal</option>
        <option value="travel" <?= ($_GET['mode'] ?? '')==='travel'?'selected':'' ?>>Travel</option>
      </select>
    </div>
    <div class="col-auto"><input class="form-control" name="tab_id" type="number" placeholder="tab_id (optional)" value="<?= htmlspecialchars($_GET['tab_id'] ?? '') ?>"></div>
    <div class="col-auto"><input class="form-control" name="category_id" type="number" placeholder="category_id (optional)" value="<?= htmlspecialchars($_GET['category_id'] ?? '') ?>"></div>
    <div class="col-auto"><input class="form-control" name="from" type="month" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>"></div>
    <div class="col-auto"><input class="form-control" name="to" type="month" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Apply</button></div>
  </form>

  <div class="mt-3">
    <canvas id="chart" height="110"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode($labels) ?>;
const dataVals = <?= json_encode(array_map(fn($v)=>$v/100, $series)) ?>; // to units
const switches = <?= json_encode($switches) ?>; // [{effective_from_utc, currency},...]

// plugin to draw vertical lines for currency switches aligned to months
const markerPlugin = {
  id: 'currencyMarkers',
  afterDatasetsDraw(chart, args, pluginOpts) {
    const {ctx, chartArea:{top,bottom}, scales:{x}} = chart;
    switches.forEach(sw => {
      const d = new Date(sw.effective_from_utc.replace(' ','T')+'Z');
      const ym = new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), 1)).toISOString().slice(0,7);
      const idx = labels.indexOf(ym);
      if (idx === -1) return;
      const xPos = x.getPixelForValue(labels[idx]);
      ctx.save(); ctx.beginPath(); ctx.moveTo(xPos, top); ctx.lineTo(xPos, bottom);
      ctx.lineWidth = 1; ctx.setLineDash([4,3]); ctx.strokeStyle = '#888'; ctx.stroke(); ctx.restore();
    });
  }
};

const chart = new Chart(document.getElementById('chart'), {
  type: 'line',
  data: { labels, datasets: [{ label: 'Monthly total', data: dataVals, tension: 0.2 }] },
  options: {
    plugins: {
      legend: { display: true },
      tooltip: { callbacks: { label: ctx => (ctx.parsed.y).toFixed(2) } }
    },
    scales: { y: { ticks: { callback: v => v.toFixed(2) } } }
  },
  plugins: [markerPlugin]
});
</script>
<?php require 'app/views/templates/footer.php'; ?>
