<?php require 'app/views/templates/header.php';
$sum=[]; foreach ($rows as $r){ $sum[$r['year_month']] = ($sum[$r['year_month']] ?? 0) + (int)$r['total_cents']; } ksort($sum);
?>
<div class="container mt-3">
  <h3>Trend (<?= htmlspecialchars($currency) ?>)</h3>
  <form class="row g-2" method="get" action="/essentials/reports/trend">
    <div class="col-auto"><label class="form-label">From</label><input class="form-control" type="month" name="from" value="<?= htmlspecialchars($fromYm) ?>"></div>
    <div class="col-auto"><label class="form-label">To</label><input class="form-control" type="month" name="to" value="<?= htmlspecialchars($toYm) ?>"></div>
    <div class="col-auto"><label class="form-label">Currency</label><input class="form-control" type="text" name="currency" value="<?= htmlspecialchars($currency) ?>" maxlength="3"></div>
    <div class="col-auto align-self-end"><button class="btn btn-outline-secondary">Apply</button></div>
  </form>
  <div class="table-responsive mt-3">
    <table class="table table-sm table-striped">
      <thead><tr><th>Month</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach ($sum as $ym=>$cents): ?><tr><td><?= htmlspecialchars($ym) ?></td><td><?= number_format($cents/100,2) . ' ' . htmlspecialchars($currency) ?></td></tr><?php endforeach; if (empty($sum)): ?>
        <tr><td colspan="2">No data.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require 'app/views/templates/footer.php'; ?>
