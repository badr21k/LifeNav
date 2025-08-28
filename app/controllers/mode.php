<?php
class mode extends Controller {

  private function db(){ return db_connect(); }
  private function user(){
    if (!isset($_SESSION['auth'])) { header('Location: /login'); exit; }
    $st=$this->db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $st->execute([$_SESSION['auth']['id']]); return $st->fetch();
  }

  /* ---- entry points ---- */
  public function normal(){ $this->tabs('normal'); }
  public function travel(){ $this->tabs('travel'); }

  // list tabs
  private function tabs($mode){
    $u=$this->user(); $this->rolloverIfNeeded($u);
    $st=$this->db()->prepare("SELECT * FROM app_tabs WHERE mode=? AND is_active=1 ORDER BY sort,id");
    $st->execute([$mode]); $tabs=$st->fetchAll();
    $title = ucfirst($mode).' mode';
    include 'app/views/mode/index.php';
  }

  // GET /mode/tab/{mode}/{tab_id}
  public function tab($mode,$tabId){
    $u=$this->user(); $this->rolloverIfNeeded($u);
    $dbh=$this->db();

    $t=$dbh->prepare("SELECT * FROM app_tabs WHERE id=? AND mode=? AND is_active=1");
    $t->execute([(int)$tabId,$mode]); $tab=$t->fetch();
    if(!$tab){ http_response_code(404); $title='Not found'; include 'app/views/errors/404.php'; return; }

    $c=$dbh->prepare("SELECT * FROM app_categories WHERE tab_id=? AND is_active=1 AND (user_id=0 OR user_id=?) ORDER BY is_custom,name");
    $c->execute([(int)$tabId,(int)$u['id']]); $categories=$c->fetchAll();

    $title = ucfirst($mode).' — '.$tab['name'];
    include 'app/views/mode/tab.php';
  }

  // POST /mode/select/{mode}/{tab_id}/{category_id}
  public function select($mode,$tabId,$categoryId){
    $u=$this->user(); csrf_verify();
    $dbh=$this->db(); $dbh->beginTransaction();
    try{
      // validate
      $s=$dbh->prepare("SELECT 1 FROM app_tabs WHERE id=? AND mode=? AND is_active=1"); $s->execute([(int)$tabId,$mode]);
      if(!$s->fetch()) throw new Exception('bad tab');
      $s=$dbh->prepare("SELECT * FROM app_categories WHERE id=? AND tab_id=? AND is_active=1 AND (user_id=0 OR user_id=?)");
      $s->execute([(int)$categoryId,(int)$tabId,(int)$u['id']]); $cat=$s->fetch();
      if(!$cat) throw new Exception('bad category');

      // ensure persistent row
      $ym=(new DateTime('now', new DateTimeZone($u['tz'])))->format('Y-m');
      $q=$dbh->prepare("SELECT * FROM app_user_category_rows WHERE user_id=? AND mode=? AND tab_id=? AND category_id=? FOR UPDATE");
      $q->execute([(int)$u['id'],$mode,(int)$tabId,(int)$categoryId]); $row=$q->fetch();
      if(!$row){
        $ins=$dbh->prepare("INSERT INTO app_user_category_rows (user_id,mode,tab_id,category_id,is_active,open_month_ym,current_total_cents,current_entry_count,lifetime_total_cents,lifetime_entry_count) VALUES (?,?,?,?,1,?,0,0,0,0)");
        $ins->execute([(int)$u['id'],$mode,(int)$tabId,(int)$categoryId,$ym]);
        $rowId=(int)$dbh->lastInsertId();
      }else{
        $rowId=(int)$row['id'];
        if(!(int)$row['is_active']) $dbh->prepare("UPDATE app_user_category_rows SET is_active=1 WHERE id=?")->execute([$rowId]);
      }
      $dbh->commit();
      header("Location: /mode/category/$mode/$rowId"); exit;
    }catch(Throwable $e){
      if($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error']='Could not select category.'; header("Location: /mode/tab/$mode/$tabId"); exit;
    }
  }

  // GET /mode/category/{mode}/{row_id}
  public function category($mode,$rowId){
    $u=$this->user(); $this->rolloverIfNeeded($u);
    $dbh=$this->db();
    $st=$dbh->prepare("SELECT r.*,t.name AS tab_name,c.name AS category_name FROM app_user_category_rows r JOIN app_tabs t ON t.id=r.tab_id JOIN app_categories c ON c.id=r.category_id WHERE r.id=? AND r.user_id=? AND r.mode=?");
    $st->execute([(int)$rowId,(int)$u['id'],$mode]); $row=$st->fetch();
    if(!$row){ http_response_code(404); $title='Not found'; include 'app/views/errors/404.php'; return; }

    $h=$dbh->prepare("SELECT * FROM app_entries WHERE row_id=? ORDER BY id DESC LIMIT 20");
    $h->execute([(int)$rowId]); $entries=$h->fetchAll();

    $title=$row['tab_name'].' — '.$row['category_name'];
    include 'app/views/mode/category.php';
  }

  // POST /mode/add/{row_id}
  public function add($rowId){
    $u=$this->user(); csrf_verify();
    $dbh=$this->db(); $dbh->beginTransaction();
    try{
      $r=$dbh->prepare("SELECT * FROM app_user_category_rows WHERE id=? AND user_id=? FOR UPDATE");
      $r->execute([(int)$rowId,(int)$u['id']]); $row=$r->fetch();
      if(!$row) throw new Exception('row');

      $amountStr=trim($_POST['amount']??''); $memo=mb_substr(trim($_POST['memo']??''),0,255);
      $date=trim($_POST['date']??'');
      $amountCents=$this->toCents($amountStr);
      if($amountCents===null || $amountCents<=0) throw new Exception('amount');

      $tz=new DateTimeZone($u['tz']); $now=new DateTime('now',$tz);
      if($date!==''){
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date) || substr($date,0,7)!==$row['open_month_ym']) throw new Exception('date');
        $localDate=$date; $tsLocal=new DateTime($date.' 12:00:00',$tz);
      }else{ $localDate=$now->format('Y-m-d'); $tsLocal=$now; }

      $tsUtc=(clone $tsLocal)->setTimezone(new DateTimeZone('UTC'));
      $cur=$u['active_currency']; $idem=$_POST['idem'] ?? bin2hex(random_bytes(8));

      $ins=$dbh->prepare("INSERT IGNORE INTO app_entries (row_id,user_id,ts_utc,local_date,amount_cents,currency,memo,source,idempotency_key) VALUES (?,?,?,?,?,?,?,'ui',?)");
      $ins->execute([(int)$row['id'],(int)$u['id'],$tsUtc->format('Y-m-d H:i:s'),$localDate,$amountCents,$cur,$memo,$idem]);

      if($ins->rowCount()>0){
        $dbh->prepare("UPDATE app_user_category_rows SET current_total_cents=current_total_cents+?, current_entry_count=current_entry_count+1, lifetime_total_cents=lifetime_total_cents+?, lifetime_entry_count=lifetime_entry_count+1, current_currency=?, last_entry_at=UTC_TIMESTAMP() WHERE id=?")
            ->execute([$amountCents,$amountCents,$cur,(int)$row['id']]);
      }
      $dbh->commit();
      $_SESSION['flash_ok']='Added.'; header("Location: /mode/category/".$row['mode']."/".$row['id']); exit;
    }catch(Throwable $e){
      if($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error']='Could not add entry.'; header("Location: /mode/category/".$row['mode']."/".$row['id']); exit;
    }
  }

  private function toCents($s){
    $s=trim(str_replace([',',' '],['',''],$s)); if($s===''||!preg_match('/^-?\d+(\.\d{1,2})?$/',$s)) return null;
    $neg=$s[0]==='-'; if($neg) $s=substr($s,1);
    if(strpos($s,'.')===false) $c=((int)$s)*100;
    else { list($w,$f)=explode('.',$s,2); $c=((int)$w)*100 + (int)str_pad(substr($f,0,2),2,'0'); }
    return $neg ? -$c : $c;
  }

  /* ---- month roll-over & snapshots ---- */
  private function rolloverIfNeeded($user){
    $dbh=$this->db(); $tz=new DateTimeZone($user['tz']); $now=new DateTime('now',$tz); $curYm=$now->format('Y-m');
    $rs=$dbh->prepare("SELECT * FROM app_user_category_rows WHERE user_id=? AND open_month_ym<>?");
    $rs->execute([(int)$user['id'],$curYm]); $rows=$rs->fetchAll(); if(!$rows) return;

    foreach($rows as $r){
      $startLocal=DateTime::createFromFormat('Y-m-d H:i:s',$r['open_month_ym'].'-01 00:00:00',$tz);
      $endLocal=(clone $startLocal)->modify('+1 month');
      $startUtc=(clone $startLocal)->setTimezone(new DateTimeZone('UTC'));
      $endUtc=(clone $endLocal)->setTimezone(new DateTimeZone('UTC'));

      $sum=$dbh->prepare("SELECT currency,SUM(amount_cents) total_cents,COUNT(*) cnt FROM app_entries WHERE row_id=? AND ts_utc>=? AND ts_utc<? GROUP BY currency");
      $sum->execute([(int)$r['id'],$startUtc->format('Y-m-d H:i:s'),$endUtc->format('Y-m-d H:i:s')]); $curTotals=$sum->fetchAll();

      $total=0; $cnt=0; $pred=null; $predVal=-1;
      foreach($curTotals as $ct){ $total+=(int)$ct['total_cents']; $cnt+=(int)$ct['cnt']; if((int)$ct['total_cents']>$predVal){$predVal=(int)$ct['total_cents'];$pred=$ct['currency'];}}

      $dbh->prepare("INSERT INTO app_monthly_snapshots (row_id,user_id,mode,tab_id,category_id,month_start,month_end,total_cents,predominant_currency,entry_count)
                     VALUES (?,?,?,?,?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE total_cents=VALUES(total_cents), predominant_currency=VALUES(predominant_currency), entry_count=VALUES(entry_count)")
          ->execute([(int)$r['id'],(int)$user['id'],$r['mode'],(int)$r['tab_id'],(int)$_]()]()
