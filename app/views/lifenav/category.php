
<?php require 'app/views/templates/header.php'; ?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0"><?= htmlspecialchars($row['category_name']) ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars($row['tab_name']) ?> - <?= ucfirst($row['mode']) ?> Mode</p>
        </div>
        <a href="/lifenav/<?= htmlspecialchars($row['mode']) ?>" class="btn btn-outline-secondary">Back to LifeNav</a>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Current Month</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-4">Total: <strong><?= number_format($row['current_total_cents'] / 100, 2) ?> <?= htmlspecialchars($currency) ?></strong></span>
                        <span class="badge bg-secondary"><?= $row['current_entry_count'] ?> entries</span>
                    </div>
                    
                    <form method="post" action="/lifenav/add_entry">
                        <?= csrf_field() ?>
                        <input type="hidden" name="row_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="idempotency_key" value="<?= uniqid('entry_', true) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Amount (<?= htmlspecialchars($currency) ?>)</label>
                            <input type="text" name="amount" class="form-control" placeholder="12.50" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Memo (optional)</label>
                            <input type="text" name="memo" class="form-control" placeholder="Optional description" maxlength="255">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Entry</button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Lifetime Stats</h5>
                </div>
                <div class="card-body">
                    <p><strong>Total:</strong> <?= number_format($row['lifetime_total_cents'] / 100, 2) ?> <?= htmlspecialchars($row['current_currency'] ?? $currency) ?></p>
                    <p><strong>Entries:</strong> <?= $row['lifetime_entry_count'] ?></p>
                    <?php if ($row['last_entry_at']): ?>
                        <p><strong>Last Entry:</strong> <?= date('M j, Y g:i A', strtotime($row['last_entry_at'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($entries)): ?>
                        <p class="text-muted">No entries yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($entries as $entry): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= number_format($entry['amount_cents'] / 100, 2) ?></strong>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($entry['currency']) ?></span>
                                        <?php if ($entry['memo']): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($entry['memo']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= date('M j, g:i A', strtotime($entry['ts_utc'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'app/views/templates/footer.php'; ?>
