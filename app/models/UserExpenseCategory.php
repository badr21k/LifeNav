
<?php
require_once __DIR__ . '/../core/database.php';

class UserExpenseCategory {
    
    public static function getOrCreateCategory($userId, $tenantId, $mode, $tab, $category, $subcategory = null, $currency = 'CAD') {
        $db = db_connect();
        
        // First try to find existing row
        $stmt = $db->prepare("
            SELECT * FROM user_expense_categories 
            WHERE user_id = ? AND mode = ? AND tab = ? AND category = ? 
            AND (subcategory = ? OR (subcategory IS NULL AND ? IS NULL))
        ");
        $stmt->execute([$userId, $mode, $tab, $category, $subcategory, $subcategory]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return $existing;
        }
        
        // Create new row
        $stmt = $db->prepare("
            INSERT INTO user_expense_categories 
            (user_id, tenant_id, mode, tab, category, subcategory, active_currency) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $tenantId, $mode, $tab, $category, $subcategory, $currency]);
        
        return self::getOrCreateCategory($userId, $tenantId, $mode, $tab, $category, $subcategory, $currency);
    }
    
    public static function addExpense($categoryRowId, $amountCents, $currency, $memo = null, $entryDate = null) {
        $db = db_connect();
        
        try {
            $db->beginTransaction();
            
            if (!$entryDate) {
                $entryDate = date('Y-m-d');
            }
            
            // Add expense entry
            $stmt = $db->prepare("
                INSERT INTO expense_entries (category_row_id, amount_cents, currency, memo, entry_date)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$categoryRowId, $amountCents, $currency, $memo, $entryDate]);
            
            // Update current month total
            $stmt = $db->prepare("
                UPDATE user_expense_categories 
                SET current_month_total_cents = current_month_total_cents + ?,
                    lifetime_total_cents = lifetime_total_cents + ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$amountCents, $amountCents, $categoryRowId]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    public static function getUserCategories($userId, $mode = null) {
        $db = db_connect();
        
        $sql = "SELECT * FROM user_expense_categories WHERE user_id = ? AND is_active = 1";
        $params = [$userId];
        
        if ($mode) {
            $sql .= " AND mode = ?";
            $params[] = $mode;
        }
        
        $sql .= " ORDER BY mode, tab, category";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function performMonthlyRollup($userId = null) {
        $db = db_connect();
        
        $currentMonth = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));
        
        // Get categories to roll up
        $sql = "
            SELECT * FROM user_expense_categories 
            WHERE current_month_total_cents > 0
        ";
        $params = [];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($categories as $category) {
            try {
                $db->beginTransaction();
                
                // Create monthly snapshot
                $stmt = $db->prepare("
                    INSERT INTO monthly_snapshots 
                    (category_row_id, user_id, mode, tab, category, subcategory, 
                     month_start, month_end, total_cents, currency_used, entry_count)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // Get entry count for the month
                $countStmt = $db->prepare("
                    SELECT COUNT(*) as count FROM expense_entries 
                    WHERE category_row_id = ? AND entry_date >= ? AND entry_date <= ?
                ");
                $countStmt->execute([$category['id'], $lastMonth, $lastMonthEnd]);
                $entryCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                $stmt->execute([
                    $category['id'],
                    $category['user_id'],
                    $category['mode'],
                    $category['tab'],
                    $category['category'],
                    $category['subcategory'],
                    $lastMonth,
                    $lastMonthEnd,
                    $category['current_month_total_cents'],
                    $category['active_currency'],
                    $entryCount
                ]);
                
                // Reset current month total
                $resetStmt = $db->prepare("
                    UPDATE user_expense_categories 
                    SET current_month_total_cents = 0, updated_at = NOW()
                    WHERE id = ?
                ");
                $resetStmt->execute([$category['id']]);
                
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Monthly rollup failed for category {$category['id']}: " . $e->getMessage());
            }
        }
    }
}
