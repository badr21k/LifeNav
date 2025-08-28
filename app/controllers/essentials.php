<?php

class Essentials extends Controller {

    private function requireAuth() {
        if (!isset($_SESSION['auth'])) {
            header('Location: /login');
            exit;
        }
    }

    private function tenantId() {
        return $_SESSION['auth']['tenant_id'] ?? 1;
    }

    private function userId() {
        return $_SESSION['auth']['id'];
    }

    // Main dashboard
    public function index() {
        $this->requireAuth();
        $userId = $this->userId();
        $mode = $_GET['mode'] ?? 'normal';

        // Check for monthly rollup
        $this->checkAndPerformMonthlyRollup();

        $userCategories = UserExpenseCategory::getUserCategories($userId, $mode);
        $allCategories = ExpenseCategories::getAllCategories();
        $userCurrency = $this->getUserCurrency();

        $title = 'Expense Tracker - ' . ucfirst($mode) . ' Mode';
        include 'app/views/essentials/dashboard.php';
    }

    // Switch between normal and travel modes
    public function mode($mode = 'normal') {
        $this->requireAuth();
        if (!in_array($mode, ['normal', 'travel'])) {
            $mode = 'normal';
        }
        header("Location: /essentials?mode=$mode");
        exit;
    }

    // Add expense to category
    public function add_expense() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /essentials');
            exit;
        }

        $userId = $this->userId();
        $tenantId = $this->tenantId();
        $mode = $_POST['mode'] ?? 'normal';
        $tab = $_POST['tab'] ?? '';
        $category = $_POST['category'] ?? '';
        $subcategory = $_POST['subcategory'] ?? null;
        $amount = floatval($_POST['amount'] ?? 0);
        $currency = $_POST['currency'] ?? $this->getUserCurrency();
        $memo = $_POST['memo'] ?? '';
        $entryDate = $_POST['entry_date'] ?? date('Y-m-d');

        if ($amount <= 0 || !$tab || !$category) {
            $_SESSION['flash_error'] = 'Invalid expense data';
            header("Location: /essentials?mode=$mode");
            exit;
        }

        try {
            // Get or create category row
            $categoryRow = UserExpenseCategory::getOrCreateCategory(
                $userId, $tenantId, $mode, $tab, $category, $subcategory, $currency
            );

            // Add expense
            $amountCents = intval($amount * 100);
            UserExpenseCategory::addExpense(
                $categoryRow['id'], $amountCents, $currency, $memo, $entryDate
            );

            $_SESSION['flash_success'] = 'Expense added successfully';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to add expense: ' . $e->getMessage();
        }

        header("Location: /essentials?mode=$mode");
        exit;
    }

    // View category details
    public function category($categoryRowId) {
        $this->requireAuth();
        $userId = $this->userId();

        $db = db_connect();
        $stmt = $db->prepare("
            SELECT * FROM user_expense_categories 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$categoryRowId, $userId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            header('Location: /essentials');
            exit;
        }

        // Get recent entries
        $stmt = $db->prepare("
            SELECT * FROM expense_entries 
            WHERE category_row_id = ? 
            ORDER BY entry_date DESC, created_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$categoryRowId]);
        $recentEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get monthly snapshots
        $stmt = $db->prepare("
            SELECT * FROM monthly_snapshots 
            WHERE category_row_id = ? 
            ORDER BY month_start DESC 
            LIMIT 12
        ");
        $stmt->execute([$categoryRowId]);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $title = 'Category Details';
        include 'app/views/essentials/category_detail.php';
    }

    // Currency management
    public function switch_currency() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /essentials');
            exit;
        }

        $userId = $this->userId();
        $newCurrency = strtoupper($_POST['currency'] ?? 'CAD');
        $currentCurrency = $this->getUserCurrency();

        if ($newCurrency !== $currentCurrency) {
            $this->recordCurrencySwitch($userId, $currentCurrency, $newCurrency);
            $this->updateUserCurrency($userId, $newCurrency);
            $_SESSION['flash_success'] = "Currency switched to $newCurrency";
        }

        header('Location: /essentials');
        exit;
    }

    // Reports and graphs
    public function reports() {
        $this->requireAuth();
        $userId = $this->userId();
        $mode = $_GET['mode'] ?? 'normal';
        $tab = $_GET['tab'] ?? null;
        $fromDate = $_GET['from'] ?? date('Y-m-01', strtotime('-11 months'));
        $toDate = $_GET['to'] ?? date('Y-m-t');

        $db = db_connect();

        // Get monthly snapshots for charts
        $sql = "
            SELECT ms.*, uec.tab, uec.category 
            FROM monthly_snapshots ms
            JOIN user_expense_categories uec ON ms.category_row_id = uec.id
            WHERE ms.user_id = ? AND ms.mode = ? 
            AND ms.month_start >= ? AND ms.month_start <= ?
        ";
        $params = [$userId, $mode, $fromDate, $toDate];

        if ($tab) {
            $sql .= " AND uec.tab = ?";
            $params[] = $tab;
        }

        $sql .= " ORDER BY ms.month_start, uec.tab, uec.category";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get currency switches in date range
        $stmt = $db->prepare("
            SELECT * FROM currency_switches 
            WHERE user_id = ? AND switch_date >= ? AND switch_date <= ?
            ORDER BY switch_date
        ");
        $stmt->execute([$userId, $fromDate, $toDate]);
        $currencySwitches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $title = 'Expense Reports';
        include 'app/views/essentials/reports.php';
    }

    // Helper methods
    private function getUserCurrency() {
        $userId = $this->userId();
        $db = db_connect();

        $stmt = $db->prepare("SELECT active_currency FROM user_settings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['active_currency'] : 'CAD';
    }

    private function updateUserCurrency($userId, $currency) {
        $db = db_connect();

        $stmt = $db->prepare("
            INSERT INTO user_settings (user_id, active_currency) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE active_currency = ?, updated_at = NOW()
        ");
        $stmt->execute([$userId, $currency, $currency]);
    }

    private function recordCurrencySwitch($userId, $fromCurrency, $toCurrency) {
        $db = db_connect();

        $stmt = $db->prepare("
            INSERT INTO currency_switches (user_id, from_currency, to_currency) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $fromCurrency, $toCurrency]);
    }

    private function checkAndPerformMonthlyRollup() {
        $currentMonth = date('Y-m');
        $lastRollup = $_SESSION['last_rollup'] ?? null;

        if ($lastRollup !== $currentMonth) {
            UserExpenseCategory::performMonthlyRollup($this->userId());
            $_SESSION['last_rollup'] = $currentMonth;
        }
    }
}