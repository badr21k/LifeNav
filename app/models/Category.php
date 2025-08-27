<?php
require_once __DIR__ . '/../core/database.php';

class Category {
  public static function allActive(): array {
    return db()->query("SELECT * FROM categories WHERE active=1 ORDER BY id")->fetchAll();
  }
  public static function idByName(string $name): ?int {
    $map = [
      'transport'=>1,'transportation'=>1,
      'accommodation'=>2,'housing'=>2,'rent'=>2,
      'travel&ent'=>3,'travel & entertainment'=>3,'travel and entertainment'=>3,'travel'=>3,'entertainment'=>3,
      'health'=>4,'medical'=>4
    ];
    $k = strtolower(trim($name));
    if (isset($map[$k])) return $map[$k];
    $st = db()->prepare("SELECT id FROM categories WHERE LOWER(name)=LOWER(?) LIMIT 1");
    $st->execute([$name]); $r=$st->fetch();
    return $r ? (int)$r['id'] : null;
  }
}
