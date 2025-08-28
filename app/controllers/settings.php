<?php
class settings extends Controller {
  private function db(){ return db_connect(); }
  private function user(){
    if (!isset($_SESSION['auth'])) { header('Location:/login'); exit; }
    $st=$this->db()->prepare("SELECT * FROM users WHERE id=?"); $st->execute([$_SESSION['auth']['id']]); return $st->fetch();
  }

  // GET /settings/currency
  public function currency(){
    $u=$this->user();
    $h=$this->db()->prepare("SELECT * FROM app_user_currency_history WHERE user_id=? ORDER BY effective_from_utc DESC");
    $h->execute([(int)$u['id']]); $history=$h->fetchAll();
    $title='Currency';
    include 'app/views/settings/currency.php';
  }

  // POST /settings/set_currency
  public function set_currency(){
    $u=$this->user(); csrf_verify();
    $cur=strtoupper(trim($_POST['currency']??''));
    if(!preg_match('/^[A-Z]{3}$/',$cur)){
      $_SESSION['flash_error']='Invalid currency code.'; header('Location:/settings/currency'); exit;
    }
    $dbh=$this->db(); $dbh->beginTransaction();
    try{
      $dbh->prepare("INSERT INTO app_user_currency_history (user_id,effective_from_utc,currency) VALUES (?,?,?)")
          ->execute([(int)$u['id'], gmdate('Y-m-d H:i:s'), $cur]);
      $dbh->prepare("UPDATE users SET active_currency=? WHERE id=?")->execute([$cur,(int)$u['id']]);
      $dbh->commit(); $_SESSION['flash_ok']='Currency changed to '.$cur.'.';
    }catch(Throwable $e){ if($dbh->inTransaction()) $dbh->rollBack(); $_SESSION['flash_error']='Update failed.'; }
    header('Location:/settings/currency'); exit;
  }
}
