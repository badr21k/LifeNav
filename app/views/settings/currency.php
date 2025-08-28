<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3" style="max-width:640px;">
  <h3>Currency</h3>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger mt-2"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success mt-2"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>

  <form class="row g-2 mt-2" method="post" action="/settings/set_currency">
    <?= csrf_field() ?>
    <div class="col-8">
      <input class="form-control" name="currency" placeholder="e.g., CAD" maxlength="3" style="text-transform:uppercase" required>
      <div class="form-text">3-letter ISO (CAD, USD, EUR, MAD, ...)</div>
    </div>
    <div class="col-4"><button class="btn btn-primary w-100">Set</button></div>
  </form>

  <h5 class="mt-4">Current</h5>
  <p class="lead mb-1"><span class="badge bg-light text-dark border"><?= htmlspecialchars($u['active_currency']) ?></span></p>

  <h5 class="mt-4">Switch history</h5>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>When (UTC)</th><th>Currency</th></tr></thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr><td><?= htmlspecialchars($h['effective_from_utc']) ?></td><td><span class="badge bg-light text-dark border"><?= htmlspecialchars($h['currency']) ?></span></td></tr>
        <?php endforeach; if (empty($history)): ?>
          <tr><td colspan="2">None yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require 'app/views/templates/footer.php'; ?>
