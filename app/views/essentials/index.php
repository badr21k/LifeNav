<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3">
  <div class="d-flex justify-content-between align-items-center">
    <h3 class="m-0">Essentials — Expenses</h3>
    <a class="btn btn-primary" href="/essentials/create">Add Expense</a>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger mt-2"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success mt-2"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>

  <form class="row g-2 mt-2" method="get" action="/essentials">
    <div class="col-auto">
      <label class="form-label">From</label>
      <input class="form-control" type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
    </div>
    <div class="col-auto">
      <label class="form-label">To</label>
      <input class="form-control" type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    </div>
    <div class="col-auto">
      <label class="form-label">Category</label>
      <select class="form-select" name="category[]" multiple size="4">
        <?php foreach ($categories as $c): $sel=in_array($c['id'], (array)($_GET['category'] ?? []))?'selected':''; ?>
          <option value="<?= (int)$c['id'] ?>" <?= $sel ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">Payment</label>
      <select class="form-select" name="payment_method[]" multiple size="5">
        <?php foreach ($pms as $p): $sel=in_array($p['id'], (array)($_GET['payment_method'] ?? []))?'selected':''; ?>
          <option value="<?= (int)$p['id'] ?>" <?= $sel ?>><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">Tag</label>
      <select class="form-select" name="tag_id">
        <option value="">— Any —</option>
        <?php foreach ($tags as $t): $sel=((string)$t['id'] === (string)($_GET['tag_id'] ?? ''))?'selected':''; ?>
          <option value="<?= (int)$t['id'] ?>" <?= $sel ?>><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto align-self-end">
      <button class="btn btn-outline-secondary">Filter</button>
    </div>
  </form>

  <div class="table-responsive mt-3">
    <table class="table table-sm table-striped align-middle">
      <thead><tr>
        <th>Date</th><th>Category</th><th>Subcategory</th><th>Merchant</th>
        <th>Note</th><th>Pay</th><th class="text-end">Amount</th><th>Tags</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['date']) ?></td>
          <td><?= htmlspecialchars($r['category_name']) ?></td>
          <td><?= htmlspecialchars($r['subcategory_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['merchant'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['note'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['payment_method_name'] ?? '—') ?></td>
          <td class="text-end"><strong><?= number_format($r['amount_cents']/100,2) . ' ' . htmlspecialchars($r['currency']) ?></strong></td>
          <td class="small">
            <?php
              $tst = db_connect()->prepare("SELECT t.name FROM tags t INNER JOIN expense_tags et ON et.tag_id=t.id WHERE et.expense_id=? ORDER BY t.name");
              $tst->execute([(int)$r['id']]); $ts=$tst->fetchAll();
              if ($ts) { foreach ($ts as $t) echo '<span class="badge bg-light text-dark border me-1">'.htmlspecialchars($t['name']).'</span>'; }
              else echo '—';
            ?>
          </td>
          <td class="text-nowrap">
            <a class="btn btn-sm btn-outline-secondary" href="/essentials/edit/<?= (int)$r['id'] ?>">Edit</a>
            <form class="d-inline" method="post" action="/essentials/delete/<?= (int)$r['id'] ?>" onsubmit="return confirm('Delete this expense?')">
              <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="9">No results.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require 'app/views/templates/footer.php'; ?>
