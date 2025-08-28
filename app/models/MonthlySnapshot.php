
<?php
require_once __DIR__ . '/../core/database.php';

class MonthlySnapshot {
    public static function getByUserAndRange(int $userId, string $fromYm, string $toYm): array {
        $st = db()->prepare("
            SELECT ms.*, t.name as tab_name, c.name as category_name
            FROM monthly_snapshots ms
            JOIN tabs t ON t.id = ms.tab_id
            JOIN categories c ON c.id = ms.category_id
            WHERE ms.user_id = ? AND DATE_FORMAT(ms.month_start, '%Y-%m') BETWEEN ? AND ?
            ORDER BY ms.month_start, t.sort, c.name
        ");
        $st->execute([$userId, $fromYm, $toYm]);
        return $st->fetchAll();
    }
    
    public static function getSubtotalsBySnapshot(int $snapshotId): array {
        $st = db()->prepare("
            SELECT * FROM monthly_snapshot_subtotals 
            WHERE snapshot_id = ? 
            ORDER BY currency
        ");
        $st->execute([$snapshotId]);
        return $st->fetchAll();
    }
}
