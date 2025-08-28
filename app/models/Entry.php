
<?php
require_once __DIR__ . '/../core/database.php';

class Entry {
    public static function getByRow(int $rowId, int $limit = 10): array {
        $st = db()->prepare("
            SELECT * FROM app_entries 
            WHERE row_id = ? 
            ORDER BY ts_utc DESC 
            LIMIT ?
        ");
        $st->execute([$rowId, $limit]);
        return $st->fetchAll();
    }
    
    public static function getByUserAndMonth(int $userId, string $yearMonth): array {
        $st = db()->prepare("
            SELECT e.*, ucr.mode, ucr.tab_id, ucr.category_id, t.name as tab_name, c.name as category_name
            FROM app_entries e
            JOIN app_user_category_rows ucr ON ucr.id = e.row_id
            JOIN app_tabs t ON t.id = ucr.tab_id
            JOIN app_categories c ON c.id = ucr.category_id
            WHERE e.user_id = ? AND DATE_FORMAT(e.local_date, '%Y-%m') = ?
            ORDER BY e.ts_utc DESC
        ");
        $st->execute([$userId, $yearMonth]);
        return $st->fetchAll();
    }
}
