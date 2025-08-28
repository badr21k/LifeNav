<?php
class lifenav extends Controller {

  private function db(){ return db(); }

  private function userOrRedirect(){
    if (!isset($_SESSION['auth'])) { header('Location:/login'); exit; }
    $u = $this->db()->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
    $u->execute([$_SESSION['auth']['id']]);
    return $u->fetch();
  }

  /* --------- routes ---------- */

  // GET /lifenav/normal
  public function normal(){ $this->page('normal'); }

  // GET /lifenav/travel
  public function travel(){ $this->page('travel'); }

  // main page
  private function page(string $mode){
    $u = $this->userOrRedirect();
    $this->rolloverIfNeeded($u);

    $dbh = $this->db();

    // tabs for mode
    $tabs = $dbh->prepare('SELECT * FROM app_tabs WHERE mode=? AND is_active=1 ORDER BY sort,id');
    $tabs->execute([$mode]);
    $tabs = $tabs->fetchAll();

    // user persistent rows for mode
    $rows = $dbh->prepare('SELECT id, tab_id, category_id, current_total_cents, current_currency
                           FROM app_user_category_rows
                           WHERE user_id=? AND mode=? AND is_active=1');
    $rows->execute([(int)$u['id'], $mode]);
    $rowsByTab = [];
    foreach ($rows->fetchAll() as $r) {
      $rowsByTab[(int)$r['tab_id']][(int)$r['category_id']] = $r; // map by category id
    }

    $mode     = $mode;
    $userId   = (int)$u['id'];
    $currency = $u['active_currency'];

    // view will fetch categories per tab (kept simple)
    require 'app/views/lifenav/index.php';
  }

  // POST /lifenav/select_category
  public function select_category(){
    csrf_verify();
    $u = $this->userOrRedirect();

    $mode  = ($_POST['mode'] ?? '') === 'travel' ? 'travel' : 'normal';
    $tabId = (int)($_POST['tab_id'] ?? 0);
    $catId = (int)($_POST['category_id'] ?? 0);

    $dbh = $this->db(); $dbh->beginTransaction();
    try {
      // validate tab+category against your schema
      $ok = $dbh->prepare('SELECT 1 FROM app_tabs WHERE id=? AND mode=? AND is_active=1');
      $ok->execute([$tabId, $mode]); if (!$ok->fetch()) throw new Exception('bad tab');

      $ok = $dbh->prepare('SELECT 1 FROM app_categories WHERE id=? AND tab_id=? AND is_active=1 AND (user_id=0 OR user_id=?)');
      $ok->execute([$catId, $tabId, (int)$u['id']]); if (!$ok->fetch()) throw new Exception('bad category');

      // ensure one persistent row
      $ym = (new DateTime('now', new DateTimeZone($u['tz'])))->format('Y-m');
      $q  = $dbh->prepare('SELECT id,is_active FROM app_user_category_rows
                           WHERE user_id=? AND mode=? AND tab_id=? AND category_id=? FOR UPDATE');
      $q->execute([(int)$u['id'], $mode, $tabId, $catId]);
      $row = $q->fetch();

      if (!$row) {
        $ins = $dbh->prepare('INSERT INTO app_user_category_rows
          (user_id,mode,tab_id,category_id,is_active,open_month_ym,current_total_cents,current_entry_count,lifetime_total_cents,lifetime_entry_count)
          VALUES (?,?,?,?,1,?,0,0,0,0)');
        $ins->execute([(int)$u['id'], $mode, $tabId, $catId, $ym]);
        $rowId = (int)$dbh->lastInsertId();
      } else {
        $rowId = (int)$row['id'];
        if (!(int)$row['is_active']) {
          $dbh->prepare('UPDATE app_user_category_rows SET is_active=1 WHERE id=?')->execute([$rowId]);
        }
      }
      $dbh->commit();
      header('Location: /lifenav/category/'.$rowId); exit;

    } catch (Throwable $e) {
      if ($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error'] = 'Could not select category.';
      header('Location: /lifenav/'.$mode); exit;
    }
  }

  // GET /lifenav/category/{rowId}
  public function category($rowId){
    $u = $this->userOrRedirect();
    $this->rolloverIfNeeded($u);

    $dbh = $this->db();
    $st = $dbh->prepare('SELECT r.*, t.name AS tab_name, c.name AS category_name
                         FROM app_user_category_rows r
                         JOIN app_tabs t ON t.id=r.tab_id
                         JOIN app_categories c ON c.id=r.category_id
                         WHERE r.id=? AND r.user_id=?');
    $st->execute([(int)$rowId, (int)$u['id']]);
    $row = $st->fetch();
    if (!$row) { http_response_code(404); require 'app/views/errors/404.php'; return; }

    $hist = $dbh->prepare('SELECT * FROM app_entries WHERE row_id=? ORDER BY id DESC LIMIT 30');
    $hist->execute([(int)$rowId]);
    $entries = $hist->fetchAll();

    require 'app/views/lifenav/category.php';
  }

  // POST /lifenav/add  (row_id hidden field)
  public function add(){
    csrf_verify();
    $u = $this->userOrRedirect();

    $rowId  = (int)($_POST['row_id'] ?? 0);
    $amount = trim($_POST['amount'] ?? '');
    $memo   = mb_substr(trim($_POST['memo'] ?? ''), 0, 255);
    $date   = trim($_POST['date'] ?? '');

    $dbh = $this->db(); $dbh->beginTransaction();
    try {
      $r = $dbh->prepare('SELECT * FROM app_user_category_rows WHERE id=? AND user_id=? FOR UPDATE');
      $r->execute([$rowId, (int)$u['id']]); $row = $r->fetch();
      if (!$row) throw new Exception('row');

      $amountCents = $this->toCents($amount);
      if ($amountCents === null || $amountCents <= 0) throw new Exception('amount');

      $tz = new DateTimeZone($u['tz']); $now = new DateTime('now', $tz);
      if ($date !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || substr($date,0,7) !== $row['open_month_ym'])
          throw new Exception('date');
        $localDate = $date; $tsLocal = new DateTime($date.' 12:00:00', $tz);
      } else {
        $localDate = $now->format('Y-m-d'); $tsLocal = $now;
      }
      $tsUtc = (clone $tsLocal)->setTimezone(new DateTimeZone('UTC'));
      $cur   = $u['active_currency'];
      $idem  = $_POST['idem'] ?? bin2hex(random_bytes(8));

      $ins = $dbh->prepare('INSERT IGNORE INTO app_entries
        (row_id,user_id,ts_utc,local_date,amount_cents,currency,memo,source,idempotency_key)
        VALUES (?,?,?,?,?,?,?,"ui",?)');
      $ins->execute([$rowId, (int)$u['id'], $tsUtc->format('Y-m-d H:i:s'), $localDate, $amountCents, $cur, $memo, $idem]);

      if ($ins->rowCount() > 0) {
        $dbh->prepare('UPDATE app_user_category_rows
                       SET current_total_cents=current_total_cents+?,
                           current_entry_count=current_entry_count+1,
                           lifetime_total_cents=lifetime_total_cents+?,
                           lifetime_entry_count=lifetime_entry_count+1,
                           current_currency=?, last_entry_at=UTC_TIMESTAMP()
                       WHERE id=?')
            ->execute([$amountCents, $amountCents, $cur, $rowId]);
      }
      $dbh->commit();
      $_SESSION['flash_ok'] = 'Added.';
      header('Location: /lifenav/category/'.$rowId); exit;

    } catch (Throwable $e) {
      if ($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error'] = 'Could not add entry.';
      header('Location: /lifenav/category/'.$rowId); exit;
    }
  }

  // POST /lifenav/switch_currency
  public function switch_currency(){
    csrf_verify();
    $u = $this->userOrRedirect();
    $cur = strtoupper(trim($_POST['currency'] ?? ''));
    if (!preg_match('/^[A-Z]{3}$/', $cur)) { $_SESSION['flash_error']='Invalid currency.'; header('Location:/lifenav/normal'); exit; }

    $dbh = $this->db(); $dbh->beginTransaction();
    try{
      $dbh->prepare('INSERT INTO app_user_currency_history (user_id,effective_from_utc,currency)
                     VALUES (?,?,?)')->execute([(int)$u['id'], gmdate('Y-m-d H:i:s'), $cur]);
      $dbh->prepare('UPDATE users SET active_currency=? WHERE id=?')->execute([$cur, (int)$u['id']]);
      $dbh->commit(); $_SESSION['flash_ok'] = 'Currency switched to '.$cur.'.';
    }catch(Throwable $e){
      if($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error'] = 'Switch failed.';
    }
    header('Location:/lifenav/normal'); exit;
  }

  /* ---- helpers ---- */
  private function toCents($s){
    $s = trim(str_replace([',',' '],['',''],$s));
    if ($s==='' || !preg_match('/^-?\d+(\.\d{1,2})?$/',$s)) return null;
    $neg = $s[0]==='-'; if($neg) $s = substr($s,1);
    if (strpos($s,'.')===false) $c = ((int)$s)*100;
    else { [$w,$f] = explode('.',$s,2); $c = ((int)$w)*100 + (int)str_pad(substr($f,0,2),2,'0'); }
    return $neg ? -$c : $c;
  }

  // month roll-over (snapshots + reset)
  private function rolloverIfNeeded($user){
    $dbh = $this->db();
    $tz  = new DateTimeZone($user['tz']);
    $now = new DateTime('now', $tz);
    $curYm = $now->format('Y-m');

    $rs = $dbh->prepare('SELECT * FROM app_user_category_rows WHERE user_id=? AND open_month_ym<>?');
    $rs->execute([(int)$user['id'], $curYm]);
    $rows = $rs->fetchAll(); if (!$rows) return;

    foreach ($rows as $r) {
      $startLocal = DateTime::createFromFormat('Y-m-d H:i:s', $r['open_month_ym'].'-01 00:00:00', $tz);
      $endLocal   = (clone $startLocal)->modify('+1 month');
      $startUtc = (clone $startLocal)->setTimezone(new DateTimeZone('UTC'));
      $endUtc   = (clone $endLocal)->setTimezone(new DateTimeZone('UTC'));

      $sum = $dbh->prepare('SELECT currency, SUM(amount_cents) total_cents, COUNT(*) cnt
                            FROM app_entries WHERE row_id=? AND ts_utc>=? AND ts_utc<? GROUP BY currency');
      $sum->execute([(int)$r['id'], $startUtc->format('Y-m-d H:i:s'), $endUtc->format('Y-m-d H:i:s')]);
      $parts = $sum->fetchAll();

      $total=0; $cnt=0; $pred=null; $best=-1;
      foreach ($parts as $p){ $total += (int)$p['total_cents']; $cnt += (int)$p['cnt']; if ((int)$p['total_cents']>$best){$best=(int)$p['total_cents']; $pred=$p['currency'];} }

      $dbh->prepare('INSERT INTO app_monthly_snapshots
        (row_id,user_id,mode,tab_id,category_id,month_start,month_end,total_cents,predominant_currency,entry_count)
        VALUES (?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE total_cents=VALUES(total_cents), predominant_currency=VALUES(predominant_currency), entry_count=VALUES(entry_count)')
          ->execute([(int)$r['id'], (int)$user['id'], $r['mode'], (int)$r['tab_id'], (int)$r['category_id'],
                     $startLocal->format('Y-m-d'), $endLocal->format('Y-m-d'), $total, $pred, $cnt]);

      $sidQ=$dbh->prepare('SELECT id FROM app_monthly_snapshots WHERE row_id=? AND month_start=?');
      $sidQ->execute([(int)$r['id'], $startLocal->format('Y-m-d')]);
      $snapshotId=(int)$sidQ->fetch()['id'];

      foreach ($parts as $p){
        $dbh->prepare('INSERT INTO app_monthly_snapshot_subtotals (snapshot_id,currency,total_cents,entry_count)
                       VALUES (?,?,?,?)
                       ON DUPLICATE KEY UPDATE total_cents=VALUES(total_cents), entry_count=VALUES(entry_count)')
            ->execute([$snapshotId, $p['currency'], (int)$p['total_cents'], (int)$p['cnt']]);
      }

      $dbh->prepare('UPDATE app_user_category_rows
                     SET current_total_cents=0, current_entry_count=0, open_month_ym=?
                     WHERE id=?')->execute([$curYm, (int)$r['id']]);
    }
  }
}
