<?php require 'app/views/templates/header.php';
$catNames=[]; foreach ($cats as $c) $catNames[(int)$c['id']]=$c['name'];
$pivot=[]; foreach ($rows as $r){ $ym=$r['year_month']; $cid=(int)$r['category_id']; $pivot[$ym][$cid]=(int)$r['total_cents']; }
$yms=array_keys($pivot); sort($yms); $catIds=array_keys($catNames);
?>
<div class="container mt-3">
  <h3>Monthly totals by category (<?= htmlspecialchars($currency) ?>)</h3>
  <form class="row g-2" method="get" action="/essentials/reports/monthly">
    <div class="col-auto"><label class="form-label">From</label><input class="form-control" type="month" name="from" value="<?= htmlspecialchars($fromYm) ?>"></div>
    <div class="col-auto"><label class="form-label">To</label><input class="form-control" type="month" name="to" value="<?= htmlspecialchars($toYm) ?>"></div>
    <div class="col-auto"><label class="form-label">Currency</label><input class="form-control" type="text" name="currency" value="<?= htmlspecialchars($currency) ?>" maxlength="3"></div>
    <div class="col-auto align-self-end"><button class="btn btn-outline-secondary">Apply</button></div>
  </form>
  <div class="table-responsive mt-3">
    <table class="table table-sm table-striped">
      <thead><tr><th>Month</th><?php foreach($catIds as $cid): ?><th><?= htmlspecialchars($catNames[$cid]) ?></th><?php endforeach; ?><th>Total</th></tr></thead>
      <tbody>
        <?php foreach ($yms as $ym): $rowTotal=0; ?><tr>
          <td><strong><?= htmlspecialchars($ym) ?></strong></td>
          <?php foreach ($catIds as $cid): $cents=$pivot[$ym][$cid]??0; $rowTotal+=$cents; ?>
            <td><?= number_format($cents/100,2) . ' ' . htmlspecialchars($currency) ?></td>
          <?php endforeach; ?>
          <td><strong><?= number_format($rowTotal/100,2) . ' ' . htmlspecialchars($currency) ?></strong></td>
        </tr><?php endforeach; if (empty($yms)): ?>
        <tr><td colspan="<?= 2+count($catIds) ?>">No data.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require 'app/views/templates/footer.php'; ?>
