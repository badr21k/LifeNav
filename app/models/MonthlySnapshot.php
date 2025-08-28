<?php
require_once __DIR__ . '/../core/database.php';

class MonthlySnapshot {
    public static function getByUserAndRange(int $userId, string $fromYm, string $toYm): array {
        $st = db()->prepare("
            SELECT ms.*, t.name as tab_name, c.name as category_name
            FROM app_monthly_snapshots ms
            JOIN app_tabs t ON t.id = ms.tab_id
            JOIN app_categories c ON c.id = ms.category_id
            WHERE ms.user_id = ? AND DATE_FORMAT(ms.month_start, '%Y-%m') BETWEEN ? AND ?
            ORDER BY ms.month_start, t.sort, c.name
        ");
        $st->execute([$userId, $fromYm, $toYm]);
        return $st->fetchAll();
    }

    public static function getSubtotalsBySnapshot(int $snapshotId): array {
        $st = db()->prepare("
            SELECT * FROM app_monthly_snapshot_subtotals 
            WHERE snapshot_id = ? 
            ORDER BY currency
        ");
        $st->execute([$snapshotId]);
        return $st->fetchAll();
    }

    public static function getByUserAndMonth(int $userId, string $yearMonth): array {
        $st = db()->prepare("
            SELECT ms.*, t.name as tab_name, c.name as category_name
            FROM app_monthly_snapshots ms
            JOIN app_tabs t ON t.id = ms.tab_id
            JOIN app_categories c ON c.id = ms.category_id
            WHERE ms.user_id = ? AND DATE_FORMAT(ms.month_start, '%Y-%m') = ?
            ORDER BY t.sort, c.name
        ");
        $st->execute([$userId, $yearMonth]);
        return $st->fetchAll();
    }
}
<?php
require_once __DIR__ . '/../core/database.php';

class MonthlySnapshot {
    public static function getByUserAndRange(int $userId, string $fromYm, string $toYm): array {
        $st = db()->prepare("
            SELECT ms.*, t.name as tab_name, c.name as category_name
            FROM monthly_snapshots ms
            JOIN tabs t ON t.id = ms.tab_id
            JOIN categories c ON c.id = ms.category_id
            WHERE ms.user_id = ? AND ms.year_month >= ? AND ms.year_month <= ?
            ORDER BY ms.year_month, t.sort, c.name
        ");
        $st->execute([$userId, $fromYm, $toYm]);
        return $st->fetchAll();
    }
    
    public static function createSnapshot(int $userId, string $mode, int $tabId, int $categoryId, string $yearMonth, int $totalCents): void {
        $st = db()->prepare("
            INSERT INTO monthly_snapshots (user_id, mode, tab_id, category_id, year_month, total_cents)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE total_cents = VALUES(total_cents)
        ");
        $st->execute([$userId, $mode, $tabId, $categoryId, $yearMonth, $totalCents]);
    }
}
