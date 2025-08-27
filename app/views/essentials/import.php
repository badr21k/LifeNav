<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3">
  <h3>Import Expenses (CSV)</h3>
  <p class="text-muted">Headers: date, amount, currency, category, subcategory, payment_method, merchant, note, tags</p>
  <form method="post" action="/essentials/do_import" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input class="form-control" type="file" name="csv" accept=".csv" required>
    <div class="mt-3">
      <button class="btn btn-primary">Upload & Import</button>
      <a class="btn btn-outline-secondary" href="/essentials">Cancel</a>
    </div>
  </form>
</div>
<?php require 'app/views/templates/footer.php'; ?>
