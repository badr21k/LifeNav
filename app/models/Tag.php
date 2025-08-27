<?php
require_once __DIR__ . '/../core/database.php';

class Tag {
  public static function findByNameTenant(string $name, int $tenantId): ?array {
    $st = db()->prepare("SELECT * FROM tags WHERE tenant_id=? AND LOWER(name)=LOWER(?) LIMIT 1");
    $st->execute([$tenantId, $name]); $r=$st->fetch(); return $r?:null;
  }
  public static function create(int $tenantId, string $name): int {
    $st = db()->prepare("INSERT INTO tags (tenant_id,name) VALUES (?,?)");
    $st->execute([$tenantId, $name]); return (int)db()->lastInsertId();
  }
  public static function allByTenant(int $tenantId): array {
    $st = db()->prepare("SELECT * FROM tags WHERE tenant_id=? ORDER BY name");
    $st->execute([$tenantId]); return $st->fetchAll();
  }
}
