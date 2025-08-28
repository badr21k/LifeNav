
<?php
require_once __DIR__ . '/../core/database.php';

class Tab {
    public static function all(): array {
        return db()->query("SELECT * FROM tabs ORDER BY mode, sort")->fetchAll();
    }
    
    public static function byMode(string $mode): array {
        $st = db()->prepare("SELECT * FROM tabs WHERE mode = ? ORDER BY sort");
        $st->execute([$mode]);
        return $st->fetchAll();
    }
    
    public static function find(int $id): ?array {
        $st = db()->prepare("SELECT * FROM tabs WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        $result = $st->fetch();
        return $result ?: null;
    }
}
