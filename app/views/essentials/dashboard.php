
<?php require_once 'app/views/templates/header.php'; ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h1>Expense Tracker</h1>
        </div>
        <div class="col-md-6 text-end">
            <!-- Mode Switch -->
            <div class="btn-group mb-2" role="group">
                <a href="/essentials/mode/normal" class="btn <?= $mode === 'normal' ? 'btn-primary' : 'btn-outline-primary' ?>">Normal</a>
                <a href="/essentials/mode/travel" class="btn <?= $mode === 'travel' ? 'btn-success' : 'btn-outline-success' ?>">Travel</a>
            </div>
            
            <!-- Currency Switch -->
            <form method="post" action="/essentials/switch_currency" class="d-inline">
                <div class="input-group" style="width: 150px; display: inline-flex;">
                    <select name="currency" class="form-select form-select-sm">
                        <option value="CAD" <?= $userCurrency === 'CAD' ? 'selected' : '' ?>>CAD</option>
                        <option value="USD" <?= $userCurrency === 'USD' ? 'selected' : '' ?>>USD</option>
                        <option value="EUR" <?= $userCurrency === 'EUR' ? 'selected' : '' ?>>EUR</option>
                        <option value="MAD" <?= $userCurrency === 'MAD' ? 'selected' : '' ?>>MAD</option>
                    </select>
                    <button class="btn btn-outline-secondary btn-sm" type="submit">Switch</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    
    <!-- Tabs for categories -->
    <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
        <?php 
        $tabs = $allCategories[$mode];
        $isFirst = true;
        foreach ($tabs as $tabKey => $tabData): 
        ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $isFirst ? 'active' : '' ?>" 
                        id="<?= $tabKey ?>-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#<?= $tabKey ?>" 
                        type="button" 
                        role="tab">
                    <?= htmlspecialchars($tabData['name']) ?>
                </button>
            </li>
        <?php 
        $isFirst = false;
        endforeach; 
        ?>
    </ul>
    
    <!-- Tab content -->
    <div class="tab-content" id="categoryTabsContent">
        <?php 
        $isFirst = true;
        foreach ($tabs as $tabKey => $tabData): 
        ?>
            <div class="tab-pane fade <?= $isFirst ? 'show active' : '' ?>" 
                 id="<?= $tabKey ?>" 
                 role="tabpanel">
                <div class="row mt-3">
                    <!-- Categories grid -->
                    <div class="col-md-8">
                        <div class="row">
                            <?php foreach ($tabData['categories'] as $categoryKey): ?>
                                <?php 
                                // Find if user has this category
                                $userCategory = null;
                                foreach ($userCategories as $uc) {
                                    if ($uc['mode'] === $mode && $uc['tab'] === $tabKey && $uc['category'] === $categoryKey) {
                                        $userCategory = $uc;
                                        break;
                                    }
                                }
                                ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card <?= $userCategory ? 'border-primary' : 'border-light' ?>">
                                        <div class="card-body p-2">
                                            <h6 class="card-title"><?= htmlspecialchars(ExpenseCategories::getCategoryDisplayName($categoryKey)) ?></h6>
                                            <?php if ($userCategory): ?>
                                                <p class="card-text text-primary mb-1">
                                                    <strong><?= $userCategory['active_currency'] ?> <?= number_format($userCategory['current_month_total_cents'] / 100, 2) ?></strong>
                                                </p>
                                                <small class="text-muted">This month</small>
                                                <div class="mt-2">
                                                    <a href="/essentials/category/<?= $userCategory['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    <button class="btn btn-sm btn-primary" onclick="showAddExpense('<?= $mode ?>', '<?= $tabKey ?>', '<?= $categoryKey ?>')">+</button>
                                                </div>
                                            <?php else: ?>
                                                <p class="card-text text-muted">Not used yet</p>
                                                <button class="btn btn-sm btn-outline-primary" onclick="showAddExpense('<?= $mode ?>', '<?= $tabKey ?>', '<?= $categoryKey ?>')">Add First</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Quick add form -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6>Add Expense</h6>
                            </div>
                            <div class="card-body">
                                <form method="post" action="/essentials/add_expense" id="expenseForm">
                                    <input type="hidden" name="mode" value="<?= $mode ?>">
                                    <input type="hidden" name="tab" id="selectedTab" value="">
                                    <input type="hidden" name="category" id="selectedCategory" value="">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Selected Category</label>
                                        <input type="text" class="form-control" id="categoryDisplay" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?= $userCurrency ?></span>
                                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Memo (optional)</label>
                                        <input type="text" name="memo" class="form-control">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    
                                    <input type="hidden" name="currency" value="<?= $userCurrency ?>">
                                    
                                    <button type="submit" class="btn btn-primary w-100" id="submitExpense" disabled>Add Expense</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php 
        $isFirst = false;
        endforeach; 
        ?>
    </div>
</div>

<script>
function showAddExpense(mode, tab, category) {
    document.getElementById('selectedTab').value = tab;
    document.getElementById('selectedCategory').value = category;
    document.getElementById('categoryDisplay').value = category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById('submitExpense').disabled = false;
}
</script>

<?php require_once 'app/views/templates/footer.php'; ?>
