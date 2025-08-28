
<?php require 'app/views/templates/header.php'; ?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="m-0"><?= htmlspecialchars($row['category_name']) ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars($row['tab_name']) ?> — <?= ucfirst($row['mode']) ?> mode</p>
        </div>
        <a href="/lifenav/<?= $row['mode'] ?>" class="btn btn-outline-secondary">← Back to categories</a>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add Entry</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/lifenav/add_entry/<?= $row['id'] ?>">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Amount</label>
                                    <input type="text" name="amount" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Memo (optional)</label>
                            <input type="text" name="memo" class="form-control" maxlength="255">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Entry</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <p><strong>Current Month:</strong><br>
                    <?= number_format($row['current_total_cents'] / 100, 2) ?> <?= htmlspecialchars($row['current_currency'] ?? 'CAD') ?><br>
                    <small class="text-muted"><?= $row['current_entry_count'] ?> entries</small></p>
                    
                    <p><strong>Lifetime:</strong><br>
                    <?= number_format($row['lifetime_total_cents'] / 100, 2) ?> <?= htmlspecialchars($row['current_currency'] ?? 'CAD') ?><br>
                    <small class="text-muted"><?= $row['lifetime_entry_count'] ?> entries</small></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($entries)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Entries</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Memo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['local_date']) ?></td>
                            <td><?= number_format($entry['amount_cents'] / 100, 2) ?> <?= htmlspecialchars($entry['currency']) ?></td>
                            <td><?= htmlspecialchars($entry['memo'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require 'app/views/templates/footer.php'; ?>
