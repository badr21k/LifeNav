<?php
class essentials extends Controller {

  private function requireAuth() {
    if (!isset($_SESSION['auth'])) { header('Location: /login'); exit; }
  }

  // GET /essentials/list — simple read-only list to verify data
  public function list() {
    $this->requireAuth();
    $dbh = db_connect();
    $tenantId = $this->tenantId();

    $st = $dbh->prepare("SELECT e.*, c.name AS category_name, sc.name AS subcategory_name, pm.name AS payment_method_name
                          FROM expenses e
                          LEFT JOIN categories c ON c.id=e.category_id
                          LEFT JOIN subcategories sc ON sc.id=e.subcategory_id
                          LEFT JOIN payment_methods pm ON pm.id=e.payment_method_id
                          WHERE e.tenant_id=? ORDER BY e.date DESC, e.id DESC LIMIT 200");
    $st->execute([$tenantId]);
    $rows = $st->fetchAll();

    $title = 'Expenses (read-only)';
    include 'app/views/essentials/list.php';
  }
  private function tenantId(): int { return (int)($_SESSION['auth']['tenant_id'] ?? 1); }
  private function userId(): int   { return (int)($_SESSION['auth']['id'] ?? 0); }

  // Check if a table has a specific column (backward compatible with pre-tenant schemas)
  private function hasColumn(PDO $dbh, string $table, string $column): bool {
    try {
      $st = $dbh->query("SHOW COLUMNS FROM `{$table}` LIKE " . $dbh->quote($column));
      return (bool)$st->fetch();
    } catch (Throwable $e) { return false; }
  }

  // GET /essentials
  public function index() {
    $this->requireAuth();
    $dbh = db_connect();
    $tenantId = $this->tenantId();

    // filters
    $from = $_GET['from'] ?? '';
    $to   = $_GET['to'] ?? '';
    $cat  = (array)($_GET['category'] ?? []);
    $pm   = (array)($_GET['payment_method'] ?? []);
    $tagId= $_GET['tag_id'] ?? '';

    // data for selects (tenant-aware if supported)
    if ($this->hasColumn($dbh,'categories','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM categories WHERE active=1 AND tenant_id=? ORDER BY id");
      $st->execute([$tenantId]);
      $categories = $st->fetchAll();
    } else {
      $categories = $dbh->query("SELECT * FROM categories WHERE active=1 ORDER BY id")->fetchAll();
    }
    if ($this->hasColumn($dbh,'payment_methods','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM payment_methods WHERE active=1 AND tenant_id=? ORDER BY id");
      $st->execute([$tenantId]);
      $pms = $st->fetchAll();
    } else {
      $pms = $dbh->query("SELECT * FROM payment_methods WHERE active=1 ORDER BY id")->fetchAll();
    }
    $st = $dbh->prepare("SELECT * FROM tags WHERE tenant_id=? ORDER BY name"); $st->execute([$tenantId]); $tags = $st->fetchAll();

    // query list
    $sql = "SELECT e.*, c.name AS category_name, sc.name AS subcategory_name, pm.name AS payment_method_name
            FROM expenses e
            LEFT JOIN categories c ON c.id=e.category_id
            LEFT JOIN subcategories sc ON sc.id=e.subcategory_id
            LEFT JOIN payment_methods pm ON pm.id=e.payment_method_id
            WHERE e.tenant_id = :tenant";
    $params = [':tenant'=>$tenantId];

    if ($from !== '') { $sql .= " AND e.date >= :from"; $params[':from']=$from; }
    if ($to !== '')   { $sql .= " AND e.date <= :to";   $params[':to']=$to; }

    if (!empty($cat)) {
      $in = implode(',', array_fill(0, count($cat), '?'));
      $sql .= " AND e.category_id IN ($in)";
    }

    if ($tagId !== '') { $sql .= " AND EXISTS (SELECT 1 FROM expense_tags et WHERE et.expense_id=e.id AND et.tag_id=:tag)"; $params[':tag']=(int)$tagId; }

    $sql .= " ORDER BY e.date DESC, e.id DESC LIMIT 300"; // simple cap
    $st = $dbh->prepare($sql);

    $i=1;
    if (!empty($cat)) { foreach ($cat as $cid) { $st->bindValue($i++, (int)$cid, PDO::PARAM_INT); } }
    foreach ($params as $k=>$v) {
      if (in_array($k, [':tenant',':tag'])) $st->bindValue($k, (int)$v, PDO::PARAM_INT);
      else $st->bindValue($k, $v);
    }
    $st->execute();
    $rows = $st->fetchAll();

    // post-filter payment method (keeps binding simple)
    if (!empty($pm)) {
      $set = array_flip(array_map('intval', $pm));
      $rows = array_values(array_filter($rows, fn($r)=> $r['payment_method_id'] ? isset($set[(int)$r['payment_method_id']]) : false));
    }

    // render
    $title = 'Essentials (Expenses)';
    include 'app/views/essentials/index.php';
  }

  // GET /essentials/create
  public function create() {
    $this->requireAuth();
    $dbh = db_connect();
    if ($this->hasColumn($dbh,'categories','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM categories WHERE active=1 AND tenant_id=? ORDER BY id");
      $st->execute([$this->tenantId()]);
      $categories = $st->fetchAll();
    } else {
      $categories = $dbh->query("SELECT * FROM categories WHERE active=1 ORDER BY id")->fetchAll();
    }

    if ($this->hasColumn($dbh,'subcategories','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM subcategories WHERE active=1 AND tenant_id=? ORDER BY category_id, name");
      $st->execute([$this->tenantId()]);
      $subRows = $st->fetchAll();
    } else {
      $subRows = $dbh->query("SELECT * FROM subcategories WHERE active=1 ORDER BY category_id, name")->fetchAll();
    }
    $subsByCat = [];
    foreach ($subRows as $r) $subsByCat[$r['category_id']][] = $r;
    if ($this->hasColumn($dbh,'payment_methods','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM payment_methods WHERE active=1 AND tenant_id=? ORDER BY id");
      $st->execute([$this->tenantId()]);
      $pms = $st->fetchAll();
    } else {
      $pms = $dbh->query("SELECT * FROM payment_methods WHERE active=1 ORDER BY id")->fetchAll();
    }
    $title = 'Add Expense';
    include 'app/views/essentials/create.php';
  }

  // POST /essentials/store
  public function store() {
    $this->requireAuth(); csrf_verify();
    $dbh = db_connect();
    $tenantId = $this->tenantId(); $userId = $this->userId();

    $date = trim($_POST['date'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $currency = strtoupper(trim($_POST['currency'] ?? 'CAD'));
    $category_id = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = ($_POST['subcategory_id'] ?? '') !== '' ? (int)$_POST['subcategory_id'] : null;
    $payment_method_id = ($_POST['payment_method_id'] ?? '') !== '' ? (int)$_POST['payment_method_id'] : null;
    $merchant = mb_substr(trim($_POST['merchant'] ?? ''), 0, 64);
    $note = mb_substr(trim($_POST['note'] ?? ''), 0, 255);
    $tags = trim($_POST['tags'] ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) { $_SESSION['flash_error']='Invalid date.'; header('Location: /essentials/create'); exit; }
    $cents = $this->toCents($amount);
    if ($cents === null || $cents <= 0) { $_SESSION['flash_error']='Invalid amount.'; header('Location: /essentials/create'); exit; }
    if ($category_id <= 0) { $_SESSION['flash_error']='Category required.'; header('Location: /essentials/create'); exit; }

    $st = $dbh->prepare("INSERT INTO expenses
      (tenant_id,user_id,date,amount_cents,currency,category_id,subcategory_id,payment_method_id,merchant,note,created_at,updated_at)
      VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())");
    $st->execute([$tenantId,$userId,$date,$cents,$currency,$category_id,$subcategory_id,$payment_method_id,$merchant,$note]);
    $id = (int)$dbh->lastInsertId();

    $this->summaryAdd($tenantId, $date, $category_id, $currency, $cents);

    if ($tags !== '') $this->attachTags($dbh, $tenantId, $id, $tags);

    $_SESSION['flash_ok']='Expense added.';
    header('Location: /essentials'); exit;
  }

  // GET /essentials/edit/{id}
  public function edit($id) {
    $this->requireAuth();
    $dbh = db_connect(); $tenantId=$this->tenantId(); $id=(int)$id;

    $st = $dbh->prepare("SELECT * FROM expenses WHERE id=? AND tenant_id=? LIMIT 1"); $st->execute([$id,$tenantId]);
    $row = $st->fetch(); if (!$row) { http_response_code(404); $title='Not Found'; include 'app/views/errors/404.php'; return; }

    if ($this->hasColumn($dbh,'categories','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM categories WHERE active=1 AND tenant_id=? ORDER BY id");
      $st->execute([$this->tenantId()]);
      $categories = $st->fetchAll();
    } else {
      $categories = $dbh->query("SELECT * FROM categories WHERE active=1 ORDER BY id")->fetchAll();
    }
    if ($this->hasColumn($dbh,'subcategories','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM subcategories WHERE active=1 AND tenant_id=? ORDER BY category_id, name");
      $st->execute([$this->tenantId()]);
      $subRows = $st->fetchAll();
    } else {
      $subRows = $dbh->query("SELECT * FROM subcategories WHERE active=1 ORDER BY category_id, name")->fetchAll();
    }
    $subsByCat=[]; foreach ($subRows as $r) $subsByCat[$r['category_id']][]=$r;
    if ($this->hasColumn($dbh,'payment_methods','tenant_id')) {
      $st = $dbh->prepare("SELECT * FROM payment_methods WHERE active=1 AND tenant_id=? ORDER BY id");
      $st->execute([$this->tenantId()]);
      $pms = $st->fetchAll();
    } else {
      $pms = $dbh->query("SELECT * FROM payment_methods WHERE active=1 ORDER BY id")->fetchAll();
    }

    $st = $dbh->prepare("SELECT t.* FROM tags t INNER JOIN expense_tags et ON et.tag_id=t.id WHERE et.expense_id=? ORDER BY t.name");
    $st->execute([$id]); $rowTags=$st->fetchAll();

    $title = 'Edit Expense';
    include 'app/views/essentials/edit.php';
  }

  // POST /essentials/update/{id}
  public function update($id) {
    $this->requireAuth(); csrf_verify();
    $dbh = db_connect(); $tenantId=$this->tenantId(); $id=(int)$id;

    $st = $dbh->prepare("SELECT * FROM expenses WHERE id=? AND tenant_id=? LIMIT 1"); $st->execute([$id,$tenantId]);
    $existing = $st->fetch(); if (!$existing) { $_SESSION['flash_error']='Not found.'; header('Location: /essentials'); exit; }

    $date = trim($_POST['date'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $currency = strtoupper(trim($_POST['currency'] ?? 'CAD'));
    $category_id = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = ($_POST['subcategory_id'] ?? '') !== '' ? (int)$_POST['subcategory_id'] : null;
    $payment_method_id = ($_POST['payment_method_id'] ?? '') !== '' ? (int)$_POST['payment_method_id'] : null;
    $merchant = mb_substr(trim($_POST['merchant'] ?? ''), 0, 64);
    $note = mb_substr(trim($_POST['note'] ?? ''), 0, 255);
    $tags = trim($_POST['tags'] ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) { $_SESSION['flash_error']='Invalid date.'; header('Location: /essentials/edit/'.$id); exit; }
    $cents = $this->toCents($amount);
    if ($cents === null || $cents <= 0) { $_SESSION['flash_error']='Invalid amount.'; header('Location: /essentials/edit/'.$id); exit; }
    if ($category_id <= 0) { $_SESSION['flash_error']='Category required.'; header('Location: /essentials/edit/'.$id); exit; }

    $st = $dbh->prepare("UPDATE expenses SET date=?, amount_cents=?, currency=?, category_id=?, subcategory_id=?, payment_method_id=?, merchant=?, note=?, updated_at=NOW()
                         WHERE id=? AND tenant_id=?");
    $st->execute([$date,$cents,$currency,$category_id,$subcategory_id,$payment_method_id,$merchant,$note,$id,$tenantId]);

    // update summaries (reverse old, add new if changed)
    $this->summaryUpdate($tenantId, $existing, [
      'date'=>$date,'amount_cents'=>$cents,'currency'=>$currency,'category_id'=>$category_id
    ]);

    // sync tags
    $this->syncTags($dbh, $tenantId, $id, $tags);

    $_SESSION['flash_ok']='Expense updated.';
    header('Location: /essentials'); exit;
  }

  // POST /essentials/delete/{id}
  public function delete($id) {
    $this->requireAuth(); csrf_verify();
    $dbh = db_connect(); $tenantId=$this->tenantId(); $id=(int)$id;

    $st = $dbh->prepare("SELECT * FROM expenses WHERE id=? AND tenant_id=? LIMIT 1");
    $st->execute([$id,$tenantId]); $row=$st->fetch();

    if ($row) {
      $dbh->prepare("DELETE FROM expenses WHERE id=? AND tenant_id=?")->execute([$id,$tenantId]);
      $this->summaryAdd($tenantId, $row['date'], $row['category_id'], $row['currency'], -1*(int)$row['amount_cents']);
      $_SESSION['flash_ok']='Expense deleted.';
    }
    header('Location: /essentials'); exit;
  }

  // GET /essentials/import
  public function import() {
    $this->requireAuth();
    $title = 'Import Expenses (CSV)';
    include 'app/views/essentials/import.php';
  }

  // POST /essentials/do_import
  public function do_import() {
    $this->requireAuth(); csrf_verify();
    if (empty($_FILES['csv']['tmp_name'])) { $_SESSION['flash_error']='Choose a CSV file.'; header('Location: /essentials/import'); exit; }

    $dbh = db_connect(); $tenantId=$this->tenantId(); $userId=$this->userId();
    $fh = fopen($_FILES['csv']['tmp_name'], 'r'); if (!$fh) { $_SESSION['flash_error']='Cannot open CSV.'; header('Location:/essentials/import'); exit; }
    $hdr = fgetcsv($fh); if (!$hdr) { $_SESSION['flash_error']='Empty CSV.'; header('Location:/essentials/import'); exit; }

    $lower = array_map(fn($h)=> strtolower(trim($h)), $hdr);
    $ix = fn($k)=> array_search($k,$lower);
    $iDate=$ix('date'); $iAmt=$ix('amount'); $iCur=$ix('currency'); $iCat=$ix('category');
    $iSub=$ix('subcategory'); $iPM=$ix('payment_method'); $iMer=$ix('merchant'); $iNote=$ix('note'); $iTags=$ix('tags');
    if ($iDate===false || $iAmt===false || $iCat===false) { $_SESSION['flash_error']='CSV needs date, amount, category'; header('Location:/essentials/import'); exit; }

    // category name -> id cache
    $catMap = $this->categoryNameMap($dbh, $tenantId);

    $total=0; $ok=0; $skip=0;
    while (($row=fgetcsv($fh))!==false) {
      $total++;
      $date = trim($row[$iDate] ?? '');
      $amountStr = trim($row[$iAmt] ?? '');
      $currency = ($iCur!==false && !empty($row[$iCur])) ? strtoupper(trim($row[$iCur])) : 'CAD';
      $catName = trim($row[$iCat] ?? '');
      $subName = ($iSub!==false ? trim($row[$iSub] ?? '') : '');
      $pmName  = ($iPM!==false ? trim($row[$iPM] ?? '') : '');
      $merchant= ($iMer!==false ? trim($row[$iMer] ?? '') : '');
      $note    = ($iNote!==false ? trim($row[$iNote] ?? '') : '');
      $tags    = ($iTags!==false ? trim($row[$iTags] ?? '') : '');

      if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) { $skip++; continue; }
      $cents = $this->toCents($amountStr);
      if ($cents===null || $cents<=0) { $skip++; continue; }

      $catId = $catMap[strtolower($catName)] ?? null; if (!$catId) { $skip++; continue; }

      $subId = null;
      if ($subName !== '') {
        if ($this->hasColumn($dbh,'subcategories','tenant_id')) {
          $st = $dbh->prepare("SELECT id FROM subcategories WHERE tenant_id=? AND category_id=? AND LOWER(name)=LOWER(?) LIMIT 1");
          $st->execute([$tenantId,$catId,$subName]); $s=$st->fetch(); $subId=$s ? (int)$s['id'] : null;
        } else {
          $st = $dbh->prepare("SELECT id FROM subcategories WHERE category_id=? AND LOWER(name)=LOWER(?) LIMIT 1");
          $st->execute([$catId,$subName]); $s=$st->fetch(); $subId=$s ? (int)$s['id'] : null;
        }
      }

      $pmId = null;
      if ($pmName !== '') {
        if ($this->hasColumn($dbh,'payment_methods','tenant_id')) {
          $st = $dbh->prepare("SELECT id FROM payment_methods WHERE tenant_id=? AND LOWER(name)=LOWER(?) LIMIT 1");
          $st->execute([$tenantId, strtolower($pmName)]);
          $row = $st->fetch();
          $pmId = $row ? (int)$row['id'] : null;
        } else {
          $m = ['cash'=>1,'debit'=>2,'credit'=>3,'e-transfer'=>4,'etransfer'=>4,'transfer'=>4,'other'=>5];
          $pmId = $m[strtolower($pmName)] ?? null;
        }
      }

      $st = $dbh->prepare("INSERT INTO expenses (tenant_id,user_id,date,amount_cents,currency,category_id,subcategory_id,payment_method_id,merchant,note,created_at,updated_at)
                           VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())");
      $st->execute([$tenantId,$userId,$date,$cents,$currency,$catId,$subId,$pmId,mb_substr($merchant,0,64),mb_substr($note,0,255)]);
      $id = (int)$dbh->lastInsertId();

      $this->summaryAdd($tenantId, $date, $catId, $currency, $cents);
      if ($tags !== '') $this->attachTags($dbh, $tenantId, $id, $tags);

      $ok++; if ($ok>=10000) break;
    }
    fclose($fh);
    $_SESSION['flash_ok'] = "Imported {$ok} rows. Skipped {$skip} of {$total}.";
    header('Location: /essentials'); exit;
  }

  // GET /essentials/reports/monthly
  public function reports_monthly() {
    $this->requireAuth();
    $dbh = db_connect(); $tenantId=$this->tenantId();
    $fromYm = $_GET['from'] ?? date('Y-m', strtotime('-5 months'));
    $toYm   = $_GET['to'] ?? date('Y-m');
    $currency = $_GET['currency'] ?? 'CAD';
    $st = $dbh->prepare("SELECT * FROM monthly_expense_totals WHERE tenant_id=? AND year_month BETWEEN ? AND ? AND currency=? ORDER BY year_month, category_id");
    $st->execute([$tenantId,$fromYm,$toYm,$currency]); $rows=$st->fetchAll();
    $cats = $dbh->query("SELECT * FROM categories WHERE active=1 ORDER BY id")->fetchAll();
    $title='Monthly totals';
    include 'app/views/essentials/reports_monthly.php';
  }

  // GET /essentials/reports/trend
  public function reports_trend() {
    $this->requireAuth();
    $dbh = db_connect(); $tenantId=$this->tenantId();
    $fromYm = $_GET['from'] ?? date('Y-m', strtotime('-11 months'));
    $toYm   = $_GET['to'] ?? date('Y-m');
    $currency = $_GET['currency'] ?? 'CAD';
    $st = $dbh->prepare("SELECT * FROM monthly_expense_totals WHERE tenant_id=? AND year_month BETWEEN ? AND ? AND currency=? ORDER BY year_month, category_id");
    $st->execute([$tenantId,$fromYm,$toYm,$currency]); $rows=$st->fetchAll();
    $title='Trend';
    include 'app/views/essentials/reports_trend.php';
  }

  /* ----------------- helpers ------------------ */
  private function toCents(string $amount): ?int {
    $amount = trim(str_replace([',',' '], ['',''], $amount));
    if ($amount === '' || !preg_match('/^-?\d+(\.\d{1,2})?$/', $amount)) return null;
    $neg = $amount[0] === '-'; if ($neg) $amount = substr($amount,1);
    if (strpos($amount, '.') === false) return ($neg?-1:1) * ((int)$amount * 100);
    [$w,$f] = explode('.', $amount, 2); $f = str_pad($f,2,'0');
    return ($neg?-1:1) * ((int)$w * 100 + (int)substr($f,0,2));
  }
  private function ym(string $date): string { return substr($date,0,7); }
  private function summaryAdd(int $tenantId, string $date, int $catId, string $currency, int $delta): void {
    $dbh = db_connect();
    $st = $dbh->prepare("INSERT INTO monthly_expense_totals (tenant_id,year_month,category_id,currency,total_cents)
                         VALUES (?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE total_cents = total_cents + VALUES(total_cents)");
    $st->execute([$tenantId,$this->ym($date),$catId,$currency,$delta]);
  }
  private function summaryUpdate(int $tenantId, array $old, array $new): void {
    $oldYm = $this->ym($old['date']); $newYm = $this->ym($new['date']);
    $dbh = db_connect();
    if ($oldYm === $newYm && $old['category_id']==$new['category_id'] && $old['currency']===$new['currency']) {
      $delta = (int)$new['amount_cents'] - (int)$old['amount_cents'];
      if ($delta !== 0) $this->summaryAdd($tenantId, $new['date'], (int)$new['category_id'], $new['currency'], $delta);
    } else {
      $this->summaryAdd($tenantId, $old['date'], (int)$old['category_id'], $old['currency'], -1*(int)$old['amount_cents']);
      $this->summaryAdd($tenantId, $new['date'], (int)$new['category_id'], $new['currency'], (int)$new['amount_cents']);
    }
  }
  private function attachTags(PDO $dbh, int $tenantId, int $expenseId, string $csv): void {
    foreach (explode(',', $csv) as $t) {
      $name = trim($t); if ($name==='') continue;
      $st = $dbh->prepare("SELECT id FROM tags WHERE tenant_id=? AND LOWER(name)=LOWER(?) LIMIT 1");
      $st->execute([$tenantId,$name]); $row=$st->fetch();
      $tagId = $row ? (int)$row['id'] : (function($dbh,$tenantId,$name){ $x=$dbh->prepare("INSERT INTO tags (tenant_id,name) VALUES (?,?)"); $x->execute([$tenantId,$name]); return (int)$dbh->lastInsertId();})($dbh,$tenantId,$name);
      $dbh->prepare("INSERT IGNORE INTO expense_tags (expense_id,tag_id) VALUES (?,?)")->execute([$expenseId,$tagId]);
    }
  }
  private function syncTags(PDO $dbh, int $tenantId, int $expenseId, string $csv): void {
    $want = [];
    foreach (explode(',', $csv) as $t) { $t=trim($t); if ($t==='') continue;
      $st=$dbh->prepare("SELECT id FROM tags WHERE tenant_id=? AND LOWER(name)=LOWER(?) LIMIT 1");
      $st->execute([$tenantId,$t]); $row=$st->fetch();
      $want[] = $row ? (int)$row['id'] : (function($dbh,$tenantId,$t){ $x=$dbh->prepare("INSERT INTO tags (tenant_id,name) VALUES (?,?)"); $x->execute([$tenantId,$t]); return (int)$dbh->lastInsertId();})($dbh,$tenantId,$t);
    }
    $want = array_values(array_unique($want));

    $st = $dbh->prepare("SELECT tag_id FROM expense_tags WHERE expense_id=?"); $st->execute([$expenseId]);
    $cur = array_map('intval', array_column($st->fetchAll(),'tag_id'));

    $toAdd = array_diff($want,$cur);
    $toDel = array_diff($cur,$want);

    foreach ($toAdd as $tid) $dbh->prepare("INSERT IGNORE INTO expense_tags (expense_id,tag_id) VALUES (?,?)")->execute([$expenseId,(int)$tid]);
    if ($toDel) {
      $in = implode(',', array_fill(0, count($toDel), '?'));
      $q  = $dbh->prepare("DELETE FROM expense_tags WHERE expense_id=? AND tag_id IN ($in)");
      $i=1; $q->bindValue($i++,$expenseId,PDO::PARAM_INT);
      foreach ($toDel as $tid) $q->bindValue($i++,(int)$tid,PDO::PARAM_INT);
      $q->execute();
    }
  }
  private function categoryNameMap(PDO $dbh, int $tenantId): array {
    if ($this->hasColumn($dbh,'categories','tenant_id')) {
      $st = $dbh->prepare("SELECT id, name FROM categories WHERE tenant_id=?");
      $st->execute([$tenantId]);
      $rows = $st->fetchAll();
    } else {
      $rows = $dbh->query("SELECT id, name FROM categories")->fetchAll();
    }
    $map = [];
    foreach ($rows as $r) $map[strtolower($r['name'])]=(int)$r['id'];
    // synonyms map to the same category id by name when present
    $aliasToName = [
      'transport' => 'transportation',
      'transportation' => 'transportation',
      'accommodation' => 'accommodation',
      'housing' => 'accommodation',
      'rent' => 'accommodation',
      'travel' => 'travel & entertainment',
      'entertainment' => 'travel & entertainment',
      'travel & entertainment' => 'travel & entertainment',
      'travel&ent' => 'travel & entertainment',
      'health' => 'health',
      'medical' => 'health',
    ];
    foreach ($aliasToName as $alias => $canonical) {
      if (isset($map[$canonical])) {
        $map[$alias] = $map[$canonical];
      }
    }
    return $map;
  }
}
