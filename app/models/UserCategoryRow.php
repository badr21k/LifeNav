
<?php
require_once __DIR__ . '/../core/database.php';

class UserCategoryRow {
    public static function findOrCreate(int $userId, string $mode, int $tabId, int $categoryId): array {
        $dbh = db();
        $dbh->beginTransaction();
        
        try {
            $st = $dbh->prepare("SELECT * FROM app_user_category_rows WHERE user_id = ? AND mode = ? AND tab_id = ? AND category_id = ? FOR UPDATE");
            $st->execute([$userId, $mode, $tabId, $categoryId]);
            $row = $st->fetch();
            
            if (!$row) {
                $currentYm = date('Y-m');
                $st = $dbh->prepare("INSERT INTO app_user_category_rows (user_id, mode, tab_id, category_id, open_month_ym, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                $st->execute([$userId, $mode, $tabId, $categoryId, $currentYm]);
                $id = $dbh->lastInsertId();
                
                $st = $dbh->prepare("SELECT * FROM app_user_category_rows WHERE id = ?");
                $st->execute([$id]);
                $row = $st->fetch();
            }
            
            $dbh->commit();
            return $row;
        } catch (Exception $e) {
            $dbh->rollback();
            throw $e;
        }
    }
    
    public static function getByUser(int $userId, string $mode): array {
        $st = db()->prepare("
            SELECT ucr.*, t.name as tab_name, c.name as category_name 
            FROM app_user_category_rows ucr 
            JOIN app_tabs t ON t.id = ucr.tab_id 
            JOIN app_categories c ON c.id = ucr.category_id 
            WHERE ucr.user_id = ? AND ucr.mode = ? AND ucr.is_active = 1 
            ORDER BY t.sort, c.name
        ");
        $st->execute([$userId, $mode]);
        return $st->fetchAll();
    }
    
    public static function addEntry(int $rowId, int $userId, int $amountCents, string $currency, string $memo = '', string $idempotencyKey = null): void {
        $dbh = db();
        $dbh->beginTransaction();
        
        try {
            // Get user timezone
            $st = $dbh->prepare("SELECT tz FROM users WHERE id = ?");
            $st->execute([$userId]);
            $user = $st->fetch();
            $tz = new DateTimeZone($user['tz'] ?? 'UTC');
            
            // Convert to local date
            $now = new DateTime('now', new DateTimeZone('UTC'));
            $localDate = $now->setTimezone($tz)->format('Y-m-d');
            
            // Insert entry (idempotent)
            $st = $dbh->prepare("
                INSERT INTO entries (row_id, user_id, ts_utc, local_date, amount_cents, currency, memo, idempotency_key, created_at, updated_at) 
                VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE id = id
            ");
            $st->execute([$rowId, $userId, $localDate, $amountCents, $currency, $memo, $idempotencyKey]);
            
            if ($st->rowCount() > 0) {
                // Update running totals
                $st = $dbh->prepare("
                    UPDATE user_category_rows 
                    SET current_total_cents = current_total_cents + ?, 
                        current_entry_count = current_entry_count + 1,
                        lifetime_total_cents = lifetime_total_cents + ?,
                        lifetime_entry_count = lifetime_entry_count + 1,
                        current_currency = ?,
                        last_entry_at = NOW(),
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $st->execute([$amountCents, $amountCents, $currency, $rowId]);
            }
            
            $dbh->commit();
        } catch (Exception $e) {
            $dbh->rollback();
            throw $e;
        }
    }
}
