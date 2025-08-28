<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3">
  <h3><?= htmlspecialchars($title) ?></h3>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger mt-2"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success mt-2"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>

  <div class="row g-3 mt-1">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <div class="text-muted">Current month total</div>
          <div class="h3 mb-0">
            <?= number_format($row['current_total_cents']/100,2) ?>
            <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['current_currency'] ?? '') ?></span>
          </div>
          <div class="small text-muted">Entries: <?= (int)$row['current_entry_count'] ?></div>
        </div>
      </div>
      <div class="card mt-3">
        <div class="card-body">
          <div class="text-muted">Lifetime total</div>
          <div class="h4 mb-0"><?= number_format($row['lifetime_total_cents']/100,2) ?></div>
          <div class="small text-muted">Entries: <?= (int)$row['lifetime_entry_count'] ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Add amount</h5>
          <form class="row g-2" method="post" action="/mode/add/<?= (int)$row['id'] ?>">
            <?= csrf_field() ?>
            <div class="col-md-4">
              <input class="form-control" name="amount" placeholder="12.50" required>
            </div>
            <div class="col-md-4">
              <input class="form-control" name="date" type="date" value="" placeholder="(optional)">
              <div class="form-text">Optional; must be within <?= htmlspecialchars($row['open_month_ym']) ?></div>
            </div>
            <div class="col-md-4">
              <input class="form-control" name="memo" placeholder="Memo (optional)" maxlength="255">
            </div>
            <div class="col-12 mt-2">
              <button class="btn btn-primary">Add</button>
              <a class="btn btn-outline-secondary" href="/mode/<?= htmlspecialchars($row['mode']) ?>">Back</a>
            </div>
          </form>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-body">
          <h5 class="card-title">History (latest)</h5>
          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead><tr><th>Date</th><th class="text-end">Amount</th><th>Cur</th><th>Memo</th></tr></thead>
              <tbody>
                <?php foreach ($entries as $e): ?>
                  <tr>
                    <td><?= htmlspecialchars($e['local_date']) ?></td>
                    <td class="text-end"><?= number_format($e['amount_cents']/100,2) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($e['currency']) ?></span></td>
                    <td><?= htmlspecialchars($e['memo'] ?? '') ?></td>
                  </tr>
                <?php endforeach; if (empty($entries)): ?>
                  <tr><td colspan="4">No entries yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php require 'app/views/templates/footer.php'; ?>
