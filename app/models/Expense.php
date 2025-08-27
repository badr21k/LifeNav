<?php
require_once __DIR__ . '/../core/database.php';

class Expense {
  public static function create(array $d): int {
    $sql = "INSERT INTO expenses
      (tenant_id,user_id,date,amount_cents,currency,category_id,subcategory_id,payment_method_id,merchant,note,created_at,updated_at)
      VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())";
    $st = db()->prepare($sql);
    $st->execute([
      $d['tenant_id'],$d['user_id'],$d['date'],$d['amount_cents'],$d['currency'],
      $d['category_id'],$d['subcategory_id'],$d['payment_method_id'],$d['merchant'],$d['note']
    ]);
    return (int)db()->lastInsertId();
  }
  public static function updateByIdTenant(int $id, int $tenantId, array $d): bool {
    $sql = "UPDATE expenses SET date=?,amount_cents=?,currency=?,category_id=?,subcategory_id=?,payment_method_id=?,merchant=?,note=?,updated_at=NOW()
            WHERE id=? AND tenant_id=?";
    $st = db()->prepare($sql);
    return $st->execute([
      $d['date'],$d['amount_cents'],$d['currency'],$d['category_id'],$d['subcategory_id'],$d['payment_method_id'],$d['merchant'],$d['note'],
      $id,$tenantId
    ]);
  }
  public static function deleteByIdTenant(int $id, int $tenantId): bool {
    $st = db()->prepare("DELETE FROM expenses WHERE id=? AND tenant_id=?");
    return $st->execute([$id,$tenantId]);
  }
  public static function findByIdTenant(int $id, int $tenantId): ?array {
    $st = db()->prepare("SELECT * FROM expenses WHERE id=? AND tenant_id=? LIMIT 1");
    $st->execute([$id,$tenantId]); $r=$st->fetch(); return $r?:null;
  }
  public static function list(array $o): array {
    $sql = "SELECT e.*, c.name AS category_name, sc.name AS subcategory_name, pm.name AS payment_method_name
            FROM expenses e
            LEFT JOIN categories c ON c.id=e.category_id
            LEFT JOIN subcategories sc ON sc.id=e.subcategory_id
            LEFT JOIN payment_methods pm ON pm.id=e.payment_method_id
            WHERE e.tenant_id = :tenant_id";
    $p = [':tenant_id'=>$o['tenant_id']];
    if (!empty($o['from'])) { $sql.=" AND e.date >= :from"; $p[':from']=$o['from']; }
    if (!empty($o['to']))   { $sql.=" AND e.date <= :to";   $p[':to']=$o['to']; }
    if (!empty($o['category_ids'])) {
      $in = implode(',', array_fill(0, count($o['category_ids']), '?'));
      $sql .= " AND e.category_id IN ($in)";
    }
    if (!empty($o['tag_id'])) { $sql .= " AND EXISTS (SELECT 1 FROM expense_tags et WHERE et.expense_id=e.id AND et.tag_id=:tag_id)"; $p[':tag_id']=$o['tag_id']; }
    $sql .= " ORDER BY e.date DESC, e.id DESC LIMIT :limit OFFSET :offset";
    $st = db()->prepare($sql);
    $i=1;
    if (!empty($o['category_ids'])) foreach ($o['category_ids'] as $cid) { $st->bindValue($i, (int)$cid, PDO::PARAM_INT); $i++; }
    foreach ($p as $k=>$v) {
      if ($k === ':tenant_id' || $k === ':tag_id') $st->bindValue($k, (int)$v, PDO::PARAM_INT);
      else $st->bindValue($k, $v);
    }
    $st->bindValue(':limit', (int)$o['limit'], PDO::PARAM_INT);
    $st->bindValue(':offset', (int)$o['offset'], PDO::PARAM_INT);
    $st->execute(); $rows=$st->fetchAll();

    // Optional payment method filter (post-filter)
    if (!empty($o['payment_method_ids'])) {
      $set = array_flip(array_map('intval', $o['payment_method_ids']));
      $rows = array_values(array_filter($rows, fn($r)=> $r['payment_method_id'] ? isset($set[(int)$r['payment_method_id']]) : false));
    }
    return $rows;
  }
}
