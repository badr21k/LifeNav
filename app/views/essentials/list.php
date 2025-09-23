<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-4">
  <h3 class="mb-3">Expenses (read-only)</h3>
  <p><a href="/essentials" class="btn btn-outline-secondary btn-sm">Back</a></p>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">No expenses found for this tenant.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-striped table-sm">
      <thead>
        <tr>
          <th>Date</th>
          <th>Amount</th>
          <th>Currency</th>
          <th>Category</th>
          <th>Subcategory</th>
          <th>Payment Method</th>
          <th>Merchant</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['date']) ?></td>
          <td><?= number_format(((int)$r['amount_cents'])/100, 2) ?></td>
          <td><?= htmlspecialchars($r['currency']) ?></td>
          <td><?= htmlspecialchars($r['category_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['subcategory_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['payment_method_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['merchant'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['note'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require 'app/views/templates/footer.php'; ?>
