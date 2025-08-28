<?php

require_once 'app/core/database.php';
require_once 'app/models/Tab.php';
require_once 'app/models/UserCategoryRow.php';
require_once 'app/models/Entry.php';
require_once 'app/models/MonthlySnapshot.php';

class LifeNav extends Controller {

    private function requireAuth() {
        if (!isset($_SESSION['auth'])) { 
            header('Location: /login'); 
            exit; 
        }
    }

    private function userId(): int { 
        return (int)($_SESSION['auth']['id'] ?? 0); 
    }

    private function userCurrency(): string {
        $st = db()->prepare("SELECT active_currency FROM users WHERE id = ?");
        $st->execute([$this->userId()]);
        $result = $st->fetch();
        return $result ? $result['active_currency'] : 'CAD';
    }

    // GET /lifenav or /lifenav/normal
    public function index($mode = 'normal') {
        $this->requireAuth();
        $userId = $this->userId();
        $mode = in_array($mode, ['normal', 'travel']) ? $mode : 'normal';

        $tabs = Tab::byMode($mode);
        $userRows = UserCategoryRow::getByUser($userId, $mode);
        $currency = $this->userCurrency();

        // Group user rows by tab
        $rowsByTab = [];
        foreach ($userRows as $row) {
            $rowsByTab[$row['tab_id']][] = $row;
        }

        $title = 'LifeNav - ' . ucfirst($mode) . ' Mode';
        include 'app/views/lifenav/index.php';
    }

    // GET /lifenav/travel
    public function travel() {
        $this->index('travel');
    }

    // GET /lifenav/category/{row_id}
    public function category($rowId) {
        $this->requireAuth();
        $userId = $this->userId();
        $rowId = (int)$rowId;

        $st = db()->prepare("
            SELECT ucr.*, t.name as tab_name, c.name as category_name, t.mode
            FROM user_category_rows ucr 
            JOIN tabs t ON t.id = ucr.tab_id 
            JOIN categories c ON c.id = ucr.category_id 
            WHERE ucr.id = ? AND ucr.user_id = ?
        ");
        $st->execute([$rowId, $userId]);
        $row = $st->fetch();

        if (!$row) {
            http_response_code(404);
            $title = 'Not Found';
            include 'app/views/errors/404.php';
            return;
        }

        $entries = Entry::getByRow($rowId, 20);
        $currency = $this->userCurrency();

        $title = $row['category_name'] . ' - ' . $row['tab_name'];
        include 'app/views/lifenav/category.php';
    }

    // POST /lifenav/add_entry
    public function add_entry() {
        $this->requireAuth();
        csrf_verify();

        $userId = $this->userId();
        $rowId = (int)($_POST['row_id'] ?? 0);
        $amount = trim($_POST['amount'] ?? '');
        $memo = trim($_POST['memo'] ?? '');
        $idempotencyKey = $_POST['idempotency_key'] ?? uniqid('entry_', true);

        // Validate amount
        $cents = $this->toCents($amount);
        if ($cents === null || $cents <= 0) {
            $_SESSION['flash_error'] = 'Invalid amount.';
            header('Location: /lifenav/category/' . $rowId);
            exit;
        }

        // Verify row belongs to user
        $st = db()->prepare("SELECT id FROM user_category_rows WHERE id = ? AND user_id = ?");
        $st->execute([$rowId, $userId]);
        if (!$st->fetch()) {
            $_SESSION['flash_error'] = 'Invalid category.';
            header('Location: /lifenav');
            exit;
        }

        try {
            $currency = $this->userCurrency();
            UserCategoryRow::addEntry($rowId, $userId, $cents, $currency, $memo, $idempotencyKey);
            $_SESSION['flash_ok'] = 'Entry added successfully.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to add entry.';
        }

        header('Location: /lifenav/category/' . $rowId);
        exit;
    }

    // POST /lifenav/select_category
    public function select_category() {
        $this->requireAuth();
        csrf_verify();

        $userId = $this->userId();
        $mode = $_POST['mode'] ?? 'normal';
        $tabId = (int)($_POST['tab_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);

        if (!in_array($mode, ['normal', 'travel']) || $tabId <= 0 || $categoryId <= 0) {
            $_SESSION['flash_error'] = 'Invalid selection.';
            header('Location: /lifenav/' . $mode);
            exit;
        }

        try {
            $row = UserCategoryRow::findOrCreate($userId, $mode, $tabId, $categoryId);
            header('Location: /lifenav/category/' . $row['id']);
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create category.';
            header('Location: /lifenav/' . $mode);
        }
        exit;
    }

    // GET /lifenav/reports/monthly
    public function reports_monthly() {
        $this->requireAuth();
        $userId = $this->userId();

        $fromYm = $_GET['from'] ?? date('Y-m', strtotime('-5 months'));
        $toYm = $_GET['to'] ?? date('Y-m');
        $mode = $_GET['mode'] ?? 'normal';

        $snapshots = MonthlySnapshot::getByUserAndRange($userId, $fromYm, $toYm);
        $snapshots = array_filter($snapshots, fn($s) => $s['mode'] === $mode);

        $title = 'Monthly Reports - ' . ucfirst($mode);
        include 'app/views/lifenav/reports_monthly.php';
    }

    // POST /lifenav/switch_currency
    public function switch_currency() {
        $this->requireAuth();
        csrf_verify();

        $userId = $this->userId();
        $newCurrency = strtoupper(trim($_POST['currency'] ?? ''));

        if (!preg_match('/^[A-Z]{3}$/', $newCurrency)) {
            $_SESSION['flash_error'] = 'Invalid currency code.';
            header('Location: /lifenav');
            exit;
        }

        $dbh = db();
        $dbh->beginTransaction();

        try {
            // Add currency history entry
            $st = $dbh->prepare("INSERT INTO user_currency_history (user_id, effective_from_utc, currency, created_at) VALUES (?, NOW(), ?, NOW())");
            $st->execute([$userId, $newCurrency]);

            // Update user's active currency
            $st = $dbh->prepare("UPDATE users SET active_currency = ?, updated_at = NOW() WHERE id = ?");
            $st->execute([$newCurrency, $userId]);

            $dbh->commit();
            $_SESSION['flash_ok'] = 'Currency switched to ' . $newCurrency;
        } catch (Exception $e) {
            $dbh->rollback();
            $_SESSION['flash_error'] = 'Failed to switch currency.';
        }

        header('Location: /lifenav');
        exit;
    }

    private function toCents(string $amount): ?int {
        $amount = trim(str_replace([',',' '], ['',''], $amount));
        if ($amount === '' || !preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            return null;
        }

        if (strpos($amount, '.') === false) {
            return (int)$amount * 100;
        }

        [$whole, $fraction] = explode('.', $amount, 2);
        $fraction = str_pad($fraction, 2, '0');
        return (int)$whole * 100 + (int)substr($fraction, 0, 2);
    }
}