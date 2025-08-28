<?php
class mode extends Controller {

  private function db(){ return db_connect(); }
  private function auth(){
    if (!isset($_SESSION['auth'])) { header('Location: /login'); exit; }
    return $_SESSION['auth'];
  }
  private function user(){ $this->auth(); $dbh=$this->db();
    $st=$dbh->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $st->execute([$_SESSION['auth']['id']]); return $st->fetch();
  }

  /* ---------- entry points ---------- */

  // GET /mode/normal
  public function normal(){ $this->tabs('normal'); }

  // GET /mode/travel
  public function travel(){ $this->tabs('travel'); }

  // GET /mode/tab/{mode}/{tab_id}
  public function tab($mode,$tabId){
    $u = $this->user();
    $this->rolloverIfNeeded($u); // month change check
    $dbh=$this->db();

    $st=$dbh->prepare("SELECT * FROM app_tabs WHERE id=? AND mode=? AND is_active=1");
    $st->execute([(int)$tabId,$mode]); $tab=$st->fetch();
    if(!$tab){ http_response_code(404); $title='Not found'; include 'app/views/errors/404.php'; return; }

    // system cats + user's customs
    $cats = $dbh->prepare("SELECT * FROM app_categories WHERE tab_id=? AND is_active=1 AND (user_id=0 OR user_id=?) ORDER BY is_custom, name");
    $cats->execute([(int)$tabId,(int)$u['id']]); $categories=$cats->fetchAll();

    $title = ucfirst($mode).' — '.$tab['name'];
    include 'app/views/mode/tab.php';
  }

  // POST /mode/select/{mode}/{tab_id}/{category_id}
  public function select($mode,$tabId,$categoryId){
    $u = $this->user(); csrf_verify();
    $dbh=$this->db(); $dbh->beginTransaction();
    try{
      // ensure tab+category valid
      $st=$dbh->prepare("SELECT 1 FROM app_tabs WHERE id=? AND mode=? AND is_active=1");
      $st->execute([(int)$tabId,$mode]); if(!$st->fetch()){ throw new Exception('Bad tab'); }
      $st=$dbh->prepare("SELECT * FROM app_categories WHERE id=? AND tab_id=? AND is_active=1 AND (user_id=0 OR user_id=?)");
      $st->execute([(int)$categoryId,(int)$tabId,(int)$u['id']]); $cat=$st->fetch(); if(!$cat){ throw new Exception('Bad category'); }

      $ym = (new DateTime('now', new DateTimeZone($u['tz'])))->format('Y-m');
      // upsert persistent row
      $q = $dbh->prepare("SELECT * FROM app_user_category_rows WHERE user_id=? AND mode=? AND tab_id=? AND category_id=? FOR UPDATE");
      $q->execute([(int)$u['id'],$mode,(int)$tabId,(int)$categoryId]); $row=$q->fetch();

      if(!$row){
        $ins=$dbh->prepare("INSERT INTO app_user_category_rows
          (user_id,mode,tab_id,category_id,is_active,open_month_ym,current_total_cents,current_entry_count,lifetime_total_cents,lifetime_entry_count)
          VALUES (?,?,?,?,1,?,0,0,0,0)");
        $ins->execute([(int)$u['id'],$mode,(int)$tabId,(int)$categoryId,$ym]);
        $rowId = (int)$dbh->lastInsertId();
      } else {
        $rowId = (int)$row['id'];
        if ((int)$row['is_active']===0){
          $dbh->prepare("UPDATE app_user_category_rows SET is_active=1 WHERE id=?")->execute([$rowId]);
        }
      }

      $dbh->commit();
      header("Location: /mode/category/$mode/$rowId"); exit;
    } catch(Throwable $e){
      if($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error']='Could not select category.';
      header("Location: /mode/tab/$mode/$tabId"); exit;
    }
  }

  // GET /mode/category/{mode}/{row_id}
  public function category($mode,$rowId){
    $u=$this->user(); $this->rolloverIfNeeded($u);
    $dbh=$this->db();

    $st=$dbh->prepare("SELECT r.*, t.name AS tab_name, c.name AS category_name
                       FROM app_user_category_rows r
                       JOIN app_tabs t ON t.id=r.tab_id
                       JOIN app_categories c ON c.id=r.category_id
                       WHERE r.id=? AND r.user_id=? AND r.mode=?");
    $st->execute([(int)$rowId,(int)$u['id'],$mode]); $row=$st->fetch();
    if(!$row){ http_response_code(404); $title='Not found'; include 'app/views/errors/404.php'; return; }

    $hist=$dbh->prepare("SELECT * FROM app_entries WHERE row_id=? ORDER BY id DESC LIMIT 20");
    $hist->execute([(int)$rowId]); $entries=$hist->fetchAll();

    $title = $row['tab_name'].' — '.$row['category_name'];
    include 'app/views/mode/category.php';
  }

  // POST /mode/add/{row_id}
  public function add($rowId){
    $u=$this->user(); csrf_verify();
    $dbh=$this->db(); $dbh->beginTransaction();
    try{
      // lock the row
      $st=$dbh->prepare("SELECT * FROM app_user_category_rows WHERE id=? AND user_id=? FOR UPDATE");
      $st->execute([(int)$rowId,(int)$u['id']]); $row=$st->fetch();
      if(!$row){ throw new Exception('row'); }

      // prepare values
      $amountStr = trim($_POST['amount'] ?? '');
      $memo = mb_substr(trim($_POST['memo'] ?? ''),0,255);
      $date = trim($_POST['date'] ?? '');  // optional backfill within current month
      $tz = new DateTimeZone($u['tz']);
      $now = new DateTime('now', $tz);
      $ymNow = $now->format('Y-m');

      $amountCents = $this->toCents($amountStr);
      if ($amountCents === null || $amountCents <= 0) { throw new Exception('amount'); }

      // enforce date within current open month in user's tz
      if ($date !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) throw new Exception('date');
        $openYm = $row['open_month_ym'];
        if (substr($date,0,7) !== $openYm) throw new Exception('date');
        $localDate = $date;
        $tsLocal = new DateTime($date.' 12:00:00', $tz); // midday safe
      } else {
        $localDate = $now->format('Y-m-d');
        $tsLocal = $now;
      }

      $tsUtc = clone $tsLocal; $tsUtc->setTimezone(new DateTimeZone('UTC'));
      $cur = $u['active_currency'];

      // idempotency (client can pass a hidden key; if not, generate)
      $idem = $_POST['idem'] ?? bin2hex(random_bytes(8));

      // insert entry (ignore if duplicate)
      $ins = $dbh->prepare("INSERT IGNORE INTO app_entries
        (row_id,user_id,ts_utc,local_date,amount_cents,currency,memo,source,idempotency_key)
        VALUES (?,?,?,?,?,?,?,'ui',?)");
      $ins->execute([(int)$row['id'],(int)$u['id'],$tsUtc->format('Y-m-d H:i:s'),$localDate,$amountCents,$cur,$memo,$idem]);

      if ($ins->rowCount() > 0) {
        // update running totals
        $dbh->prepare("UPDATE app_user_category_rows
                       SET current_total_cents = current_total_cents + ?,
                           current_entry_count = current_entry_count + 1,
                           lifetime_total_cents = lifetime_total_cents + ?,
                           lifetime_entry_count = lifetime_entry_count + 1,
                           current_currency = ?, last_entry_at = UTC_TIMESTAMP()
                       WHERE id=?")->execute([$amountCents,$amountCents,$cur,(int)$row['id']]);
      }

      $dbh->commit();
      $_SESSION['flash_ok']='Added.';
      header("Location: /mode/category/".$row['mode']."/".$row['id']); exit;

    } catch(Throwable $e){
      if($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error']='Could not add entry.';
      header("Location: /mode/category/".$row['mode']."/".$row['id']); exit;
    }
  }

  /* ---------- helpers ---------- */

  private function tabs($mode){
    $this->auth();
    $dbh=$this->db();
    $st=$dbh->prepare("SELECT * FROM app_tabs WHERE mode=? AND is_active=1 ORDER BY sort, id");
    $st->execute([$mode]); $tabs=$st->fetchAll();
    $title = ucfirst($mode).' mode';
    include 'app/views/mode/index.php';
  }

  private function toCents($amount){
    $s = trim(str_replace([',',' '],['',''],$amount));
    if ($s === '' || !preg_match('/^-?\d+(\.\d{1,2})?$/',$s)) return null;
    $neg = $s[0] === '-'; if ($neg) $s = substr($s,1);
    if (strpos($s,'.')===false) $c = ((int)$s)*100;
    else { list($w,$f)=explode('.',$s,2); $c=((int)$w)*100 + (int)str_pad(substr($f,0,2),2,'0'); }
    return $neg ? -$c : $c;
  }

  // run when the month changed for any row; creates snapshots + resets
  private function rolloverIfNeeded($user){
    $dbh=$this->db();
    $tz = new DateTimeZone($user['tz']);
    $now = new DateTime('now', $tz);
    $curYm = $now->format('Y-m');

    // find rows not matching current month
    $rs = $dbh->prepare("SELECT * FROM app_user_category_rows WHERE user_id=? AND open_month_ym<>?");
    $rs->execute([(int)$user['id'],$curYm]); $rows=$rs->fetchAll();
    if (!$rows) return;

    foreach ($rows as $r){
      $closedYm = $r['open_month_ym'];
      // month window in local time
      $startLocal = DateTime::createFromFormat('Y-m-d H:i:s', $closedYm.'-01 00:00:00', $tz);
      $endLocal = (clone $startLocal)->modify('+1 month');
      $startUtc = (clone $startLocal)->setTimezone(new DateTimeZone('UTC'));
      $endUtc   = (clone $endLocal)->setTimezone(new DateTimeZone('UTC'));

      // sums per currency from authoritative entries
      $sum = $dbh->prepare("SELECT currency, SUM(amount_cents) total_cents, COUNT(*) cnt
                            FROM app_entries
                            WHERE row_id=? AND ts_utc>=? AND ts_utc<?
                            GROUP BY currency");
      $sum->execute([(int)$r['id'],$startUtc->format('Y-m-d H:i:s'),$endUtc->format('Y-m-d H:i:s')]);
      $curTotals = $sum->fetchAll();

      $totalAll = 0; $countAll = 0; $predCur = null; $predVal = -1;
      foreach ($curTotals as $ct){ $totalAll += (int)$ct['total_cents']; $countAll += (int)$ct['cnt'];
        if ((int)$ct['total_cents'] > $predVal){ $predVal=(int)$ct['total_cents']; $predCur=$ct['currency']; } }

      // snapshot upsert
      $insSnap = $dbh->prepare("INSERT INTO app_monthly_snapshots
        (row_id,user_id,mode,tab_id,category_id,month_start,month_end,total_cents,predominant_currency,entry_count)
        VALUES (?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE total_cents=VALUES(total_cents), predominant_currency=VALUES(predominant_currency), entry_count=VALUES(entry_count)");
      $insSnap->execute([(int)$r['id'],(int)$user['id'],$r['mode'],(int)$r['tab_id'],(int)$r['category_id'],
        $startLocal->format('Y-m-d'), $endLocal->format('Y-m-d'), $totalAll, $predCur, $countAll]);
      $snapshotId = (int)$dbh->lastInsertId();
      if ($snapshotId===0){
        // need id: fetch it
        $g=$dbh->prepare("SELECT id FROM app_monthly_snapshots WHERE row_id=? AND month_start=?");
        $g->execute([(int)$r['id'],$startLocal->format('Y-m-d')]); $x=$g->fetch(); $snapshotId=(int)$x['id'];
      }

      // per-currency subtotals
      foreach ($curTotals as $ct){
        $dbh->prepare("INSERT INTO app_monthly_snapshot_subtotals (snapshot_id,currency,total_cents,entry_count)
                       VALUES (?,?,?,?)
                       ON DUPLICATE KEY UPDATE total_cents=VALUES(total_cents), entry_count=VALUES(entry_count)")
            ->execute([$snapshotId, $ct['currency'], (int)$ct['total_cents'], (int)$ct['cnt']]);
      }

      // reset the row for new month
      $dbh->prepare("UPDATE app_user_category_rows
                     SET current_total_cents=0, current_entry_count=0, open_month_ym=?
                     WHERE id=?")->execute([$curYm,(int)$r['id']]);
    }
  }
}
