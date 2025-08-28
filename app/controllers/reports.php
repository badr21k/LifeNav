<?php
class reports extends Controller {
  private function db(){ return db_connect(); }
  private function user(){
    if (!isset($_SESSION['auth'])) { header('Location:/login'); exit; }
    $st=$this->db()->prepare("SELECT * FROM users WHERE id=?"); $st->execute([$_SESSION['auth']['id']]); return $st->fetch();
  }

  // GET /reports/overview?mode=normal|travel&tab_id=&category_id=&from=YYYY-MM&to=YYYY-MM
  public function overview(){
    $u=$this->user(); $dbh=$this->db();

    $mode = ($_GET['mode'] ?? 'normal') === 'travel' ? 'travel' : 'normal';
    $tabId = (int)($_GET['tab_id'] ?? 0);
    $catId = (int)($_GET['category_id'] ?? 0);
    $fromYm = preg_match('/^\d{4}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m', strtotime('-11 months'));
    $toYm   = preg_match('/^\d{4}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : date('Y-m');

    // build month list
    $labels=[]; $cur=strtotime($fromYm.'-01'); $end=strtotime($toYm.'-01');
    while($cur <= $end){ $labels[] = date('Y-m', $cur); $cur = strtotime('+1 month', $cur); }

    // get snapshots for the user and selection
    $params=[(int)$u['id']];
    $where="s.user_id=? AND s.month_start>=? AND s.month_start<?";
    $params[] = $fromYm.'-01';
    $params[] = date('Y-m-01', strtotime($toYm.'-01 +1 month'));
    if ($mode) { $where.=" AND s.mode=?"; $params[]=$mode; }
    if ($tabId) { $where.=" AND s.tab_id=?"; $params[]=$tabId; }
    if ($catId) { $where.=" AND s.category_id=?"; $params[]=$catId; }

    $sql="SELECT DATE_FORMAT(s.month_start,'%Y-%m') ym, SUM(s.total_cents) total
          FROM app_monthly_snapshots s
          WHERE $where
          GROUP BY ym ORDER BY ym";
    $st=$dbh->prepare($sql); $st->execute($params); $rows=$st->fetchAll();
    $map=[]; foreach($rows as $r){ $map[$r['ym']] = (int)$r['total']; }
    $series=[]; foreach($labels as $ym){ $series[] = (int)($map[$ym] ?? 0); }

    // currency switch markers within range
    $hs=$dbh->prepare("SELECT effective_from_utc,currency FROM app_user_currency_history WHERE user_id=? AND effective_from_utc>=? AND effective_from_utc<? ORDER BY effective_from_utc");
    $hs->execute([(int)$u['id'], $fromYm.'-01 00:00:00', date('Y-m-01 00:00:00', strtotime($toYm.'-01 +1 month'))]);
    $switches=$hs->fetchAll();

    $title='Reports — Overview';
    include 'app/views/reports/overview.php';
  }
}
