<?php
require_once __DIR__ . '/../core/database.php';

class Subcategory {
  public static function byCategory(int $catId): array {
    $st = db()->prepare("SELECT * FROM subcategories WHERE category_id=? AND active=1 ORDER BY name");
    $st->execute([$catId]); return $st->fetchAll();
  }
  public static function allByCategory(): array {
    $rows = db()->query("SELECT * FROM subcategories WHERE active=1 ORDER BY category_id, name")->fetchAll();
    $map=[]; foreach ($rows as $r) $map[$r['category_id']][]=$r; return $map;
  }
}
