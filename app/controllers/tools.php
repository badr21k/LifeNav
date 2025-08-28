<?php
class tools extends Controller {
  public function custom_category(){
    header('Content-Type: application/json');
    if (!isset($_SESSION['auth'])) { echo json_encode(['ok'=>false,'error'=>'auth']); return; }
    $in=json_decode(file_get_contents('php://input'),true);
    $tabId=(int)($in['tab_id']??0); $name=trim($in['name']??'');
    if(!$tabId || $name===''){ echo json_encode(['ok'=>false,'error'=>'bad']); return; }
    $dbh=db_connect();
    $t=$dbh->prepare("SELECT 1 FROM app_tabs WHERE id=? AND is_active=1"); $t->execute([$tabId]); if(!$t->fetch()){ echo json_encode(['ok'=>false,'error'=>'tab']); return; }
    $dbh->prepare("INSERT INTO app_categories (tab_id,name,is_custom,user_id,is_active) VALUES (?,?,1,?,1) ON DUPLICATE KEY UPDATE is_active=1")
        ->execute([$tabId,$name,(int)$_SESSION['auth']['id']]);
    $g=$dbh->prepare("SELECT id FROM app_categories WHERE tab_id=? AND name=? AND user_id=?"); $g->execute([$tabId,$name,(int)$_SESSION['auth']['id']]); $row=$g->fetch();
    echo json_encode(['ok'=>true,'category_id'=>(int)$row['id']]);
  }
}
