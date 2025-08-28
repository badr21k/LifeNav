
<?php require 'app/views/templates/header.php'; ?>

<div class="container mt-3">
    <h3>Monthly Reports - <?= ucfirst($mode) ?></h3>
    
    <form class="row g-2 mb-4" method="get" action="/lifenav/reports/monthly">
        <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
        <div class="col-auto">
            <label class="form-label">From</label>
            <input class="form-control" type="month" name="from" value="<?= htmlspecialchars($fromYm) ?>">
        </div>
        <div class="col-auto">
            <label class="form-label">To</label>
            <input class="form-control" type="month" name="to" value="<?= htmlspecialchars($toYm) ?>">
        </div>
        <div class="col-auto align-self-end">
            <button class="btn btn-outline-secondary">Apply</button>
        </div>
        <div class="col-auto align-self-end">
            <a href="/lifenav/<?= htmlspecialchars($mode) ?>" class="btn btn-outline-primary">Back to LifeNav</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Tab</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>Entries</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($snapshots)): ?>
                    <tr><td colspan="6">No data for selected period.</td></tr>
                <?php else: ?>
                    <?php foreach ($snapshots as $snapshot): ?>
                        <tr>
                            <td><?= date('M Y', strtotime($snapshot['month_start'])) ?></td>
                            <td><?= htmlspecialchars($snapshot['tab_name']) ?></td>
                            <td><?= htmlspecialchars($snapshot['category_name']) ?></td>
                            <td><?= number_format($snapshot['total_cents'] / 100, 2) ?></td>
                            <td><?= htmlspecialchars($snapshot['predominant_currency']) ?></td>
                            <td><?= $snapshot['entry_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'app/views/templates/footer.php'; ?>
