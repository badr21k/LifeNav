
<?php require 'app/views/templates/header.php'; ?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="m-0">LifeNav</h3>
            <div class="btn-group" role="group" aria-label="Mode Selection">
                <a href="/lifenav/normal" class="btn <?= $mode === 'normal' ? 'btn-primary' : 'btn-outline-primary' ?>">Normal</a>
                <a href="/lifenav/travel" class="btn <?= $mode === 'travel' ? 'btn-primary' : 'btn-outline-primary' ?>">Travel</a>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form method="post" action="/lifenav/switch_currency" class="d-inline-flex align-items-center gap-2">
                <?= csrf_field() ?>
                <label class="form-label mb-0">Currency:</label>
                <input type="text" name="currency" value="<?= htmlspecialchars($currency) ?>" maxlength="3" class="form-control form-control-sm" style="width: 70px;">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Switch</button>
            </form>
            <a href="/lifenav/reports/monthly?mode=<?= $mode ?>" class="btn btn-outline-info btn-sm">Reports</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
    <?php endif; ?>

    <?php foreach ($tabs as $tab): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= htmlspecialchars($tab['name']) ?></h5>
            </div>
            <div class="card-body">
                <?php
                $categories = [];
                $st = db()->prepare("SELECT * FROM categories WHERE tab_id = ? AND (is_custom = 0 OR user_id = ?) AND active = 1 ORDER BY is_custom, name");
                $st->execute([$tab['id'], $userId]);
                $categories = $st->fetchAll();

                $userRowsForTab = $rowsByTab[$tab['id']] ?? [];
                $activeCategories = array_column($userRowsForTab, 'category_id');
                ?>

                <div class="row g-2">
                    <?php foreach ($categories as $category): ?>
                        <?php $isActive = in_array($category['id'], $activeCategories); ?>
                        <?php if ($isActive): ?>
                            <?php
                            $userRow = array_filter($userRowsForTab, fn($r) => $r['category_id'] == $category['id'])[0];
                            $total = number_format($userRow['current_total_cents'] / 100, 2);
                            ?>
                            <div class="col-auto">
                                <a href="/lifenav/category/<?= $userRow['id'] ?>" class="btn btn-success position-relative">
                                    <?= htmlspecialchars($category['name']) ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-light text-dark">
                                        <?= $total ?> <?= htmlspecialchars($currency) ?>
                                    </span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="col-auto">
                                <form method="post" action="/lifenav/select_category" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
                                    <input type="hidden" name="tab_id" value="<?= $tab['id'] ?>">
                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require 'app/views/templates/footer.php'; ?>
