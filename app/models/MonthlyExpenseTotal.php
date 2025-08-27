<?php
require_once __DIR__ . '/../core/database.php';

class MonthlyExpenseTotal {
  public static function upsert(int $tenantId, string $ym, int $catId, string $currency, int $delta): void {
    $st = db()->prepare("INSERT INTO monthly_expense_totals (tenant_id,year_month,category_id,currency,total_cents)
                         VALUES (?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE total_cents = total_cents + VALUES(total_cents)");
    $st->execute([$tenantId,$ym,$catId,$currency,$delta]);
  }
  public static function setValue(int $tenantId, string $ym, int $catId, string $currency, int $cents): void {
    $st = db()->prepare("INSERT INTO monthly_expense_totals (tenant_id,year_month,category_id,currency,total_cents)
                         VALUES (?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE total_cents = VALUES(total_cents)");
    $st->execute([$tenantId,$ym,$catId,$currency,$cents]);
  }
  public static function byRange(int $tenantId, string $fromYm, string $toYm, ?string $currency): array {
    $sql = "SELECT * FROM monthly_expense_totals WHERE tenant_id=? AND year_month BETWEEN ? AND ?";
    $params = [$tenantId, $fromYm, $toYm];
    if ($currency) { $sql .= " AND currency=?"; $params[]=$currency; }
    $sql .= " ORDER BY year_month, category_id";
    $st = db()->prepare($sql); $st->execute($params); return $st->fetchAll();
  }
}
