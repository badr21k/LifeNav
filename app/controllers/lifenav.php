
<?php
class lifenav extends Controller {

  private function db(){ return db_connect(); }
  private function user(){
    if (!isset($_SESSION['auth'])) { header('Location: /login'); exit; }
    $st=$this->db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $st->execute([$_SESSION['auth']['id']]); return $st->fetch();
  }

  public function index() { $this->mode('normal'); }
  public function normal() { $this->mode('normal'); }
  public function travel() { $this->mode('travel'); }

  private function mode($mode) {
    $u = $this->user();
    $dbh = $this->db();
    
    // Get tabs for this mode
    $st = $dbh->prepare("SELECT * FROM app_tabs WHERE mode=? AND is_active=1 ORDER BY sort, id");
    $st->execute([$mode]);
    $tabs = $st->fetchAll();
    
    // Get user's active category rows grouped by tab
    $st = $dbh->prepare("SELECT * FROM app_user_category_rows WHERE user_id=? AND mode=? AND is_active=1");
    $st->execute([(int)$u['id'], $mode]);
    $userRows = $st->fetchAll();
    
    $rowsByTab = [];
    foreach ($userRows as $row) {
      $rowsByTab[$row['tab_id']][] = $row;
    }
    
    $userId = (int)$u['id'];
    $currency = $u['active_currency'];
    $title = ucfirst($mode) . ' - LifeNav';
    
    include 'app/views/lifenav/index.php';
  }

  public function select_category() {
    $u = $this->user();
    csrf_verify();
    
    $mode = $_POST['mode'] ?? '';
    $tabId = (int)($_POST['tab_id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    
    $dbh = $this->db();
    $dbh->beginTransaction();
    
    try {
      // Validate tab and category
      $st = $dbh->prepare("SELECT 1 FROM app_tabs WHERE id=? AND mode=? AND is_active=1");
      $st->execute([$tabId, $mode]);
      if (!$st->fetch()) throw new Exception('Invalid tab');
      
      $st = $dbh->prepare("SELECT * FROM app_categories WHERE id=? AND tab_id=? AND is_active=1 AND (user_id=0 OR user_id=?)");
      $st->execute([$categoryId, $tabId, (int)$u['id']]);
      if (!$st->fetch()) throw new Exception('Invalid category');
      
      // Create or activate user category row
      $ym = (new DateTime('now', new DateTimeZone($u['tz'])))->format('Y-m');
      $st = $dbh->prepare("SELECT * FROM app_user_category_rows WHERE user_id=? AND mode=? AND tab_id=? AND category_id=? FOR UPDATE");
      $st->execute([(int)$u['id'], $mode, $tabId, $categoryId]);
      $row = $st->fetch();
      
      if (!$row) {
        $st = $dbh->prepare("INSERT INTO app_user_category_rows (user_id, mode, tab_id, category_id, is_active, open_month_ym, current_total_cents, current_entry_count, lifetime_total_cents, lifetime_entry_count) VALUES (?, ?, ?, ?, 1, ?, 0, 0, 0, 0)");
        $st->execute([(int)$u['id'], $mode, $tabId, $categoryId, $ym]);
        $rowId = (int)$dbh->lastInsertId();
      } else {
        $rowId = (int)$row['id'];
        if (!(int)$row['is_active']) {
          $dbh->prepare("UPDATE app_user_category_rows SET is_active=1 WHERE id=?")->execute([$rowId]);
        }
      }
      
      $dbh->commit();
      header("Location: /lifenav/category/$rowId");
      exit;
      
    } catch (Throwable $e) {
      if ($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error'] = 'Could not select category.';
      header("Location: /lifenav/$mode");
      exit;
    }
  }

  public function category($rowId) {
    $u = $this->user();
    $dbh = $this->db();
    
    $st = $dbh->prepare("SELECT r.*, t.name AS tab_name, c.name AS category_name FROM app_user_category_rows r JOIN app_tabs t ON t.id=r.tab_id JOIN app_categories c ON c.id=r.category_id WHERE r.id=? AND r.user_id=?");
    $st->execute([(int)$rowId, (int)$u['id']]);
    $row = $st->fetch();
    
    if (!$row) {
      http_response_code(404);
      $title = 'Not found';
      include 'app/views/errors/404.php';
      return;
    }
    
    // Get recent entries
    $st = $dbh->prepare("SELECT * FROM app_entries WHERE row_id=? ORDER BY id DESC LIMIT 20");
    $st->execute([(int)$rowId]);
    $entries = $st->fetchAll();
    
    $title = $row['tab_name'] . ' — ' . $row['category_name'];
    include 'app/views/lifenav/category.php';
  }

  public function add_entry($rowId) {
    $u = $this->user();
    csrf_verify();
    
    $dbh = $this->db();
    $dbh->beginTransaction();
    
    try {
      $st = $dbh->prepare("SELECT * FROM app_user_category_rows WHERE id=? AND user_id=? FOR UPDATE");
      $st->execute([(int)$rowId, (int)$u['id']]);
      $row = $st->fetch();
      if (!$row) throw new Exception('Invalid row');
      
      $amountStr = trim($_POST['amount'] ?? '');
      $memo = mb_substr(trim($_POST['memo'] ?? ''), 0, 255);
      $date = trim($_POST['date'] ?? '');
      
      $amountCents = $this->toCents($amountStr);
      if ($amountCents === null || $amountCents <= 0) throw new Exception('Invalid amount');
      
      $tz = new DateTimeZone($u['tz']);
      $now = new DateTime('now', $tz);
      
      if ($date !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || substr($date, 0, 7) !== $row['open_month_ym']) {
          throw new Exception('Invalid date');
        }
        $localDate = $date;
        $tsLocal = new DateTime($date . ' 12:00:00', $tz);
      } else {
        $localDate = $now->format('Y-m-d');
        $tsLocal = $now;
      }
      
      $tsUtc = (clone $tsLocal)->setTimezone(new DateTimeZone('UTC'));
      $currency = $u['active_currency'];
      $idem = $_POST['idem'] ?? bin2hex(random_bytes(8));
      
      $st = $dbh->prepare("INSERT IGNORE INTO app_entries (row_id, user_id, ts_utc, local_date, amount_cents, currency, memo, source, idempotency_key) VALUES (?, ?, ?, ?, ?, ?, ?, 'ui', ?)");
      $st->execute([(int)$row['id'], (int)$u['id'], $tsUtc->format('Y-m-d H:i:s'), $localDate, $amountCents, $currency, $memo, $idem]);
      
      if ($st->rowCount() > 0) {
        $dbh->prepare("UPDATE app_user_category_rows SET current_total_cents=current_total_cents+?, current_entry_count=current_entry_count+1, lifetime_total_cents=lifetime_total_cents+?, lifetime_entry_count=lifetime_entry_count+1, current_currency=?, last_entry_at=UTC_TIMESTAMP() WHERE id=?")
            ->execute([$amountCents, $amountCents, $currency, (int)$row['id']]);
      }
      
      $dbh->commit();
      $_SESSION['flash_ok'] = 'Entry added successfully.';
      header("Location: /lifenav/category/" . $row['id']);
      exit;
      
    } catch (Throwable $e) {
      if ($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error'] = 'Could not add entry.';
      header("Location: /lifenav/category/$rowId");
      exit;
    }
  }

  private function toCents($s) {
    $s = trim(str_replace([',', ' '], ['', ''], $s));
    if ($s === '' || !preg_match('/^-?\d+(\.\d{1,2})?$/', $s)) return null;
    
    $neg = $s[0] === '-';
    if ($neg) $s = substr($s, 1);
    
    if (strpos($s, '.') === false) {
      $c = ((int)$s) * 100;
    } else {
      list($w, $f) = explode('.', $s, 2);
      $c = ((int)$w) * 100 + (int)str_pad(substr($f, 0, 2), 2, '0');
    }
    
    return $neg ? -$c : $c;
  }

  public function switch_currency() {
    $u = $this->user();
    csrf_verify();
    
    $currency = strtoupper(trim($_POST['currency'] ?? ''));
    if (strlen($currency) === 3) {
      $dbh = $this->db();
      $dbh->prepare("UPDATE users SET active_currency=? WHERE id=?")->execute([$currency, (int)$u['id']]);
      $_SESSION['flash_ok'] = 'Currency updated.';
    } else {
      $_SESSION['flash_error'] = 'Invalid currency code.';
    }
    
    $mode = $_GET['mode'] ?? 'normal';
    header("Location: /lifenav/$mode");
    exit;
  }
}
