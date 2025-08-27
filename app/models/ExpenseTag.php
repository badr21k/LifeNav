<?php
require_once __DIR__ . '/../core/database.php';

class ExpenseTag {
  public static function tagsForExpense(int $expenseId): array {
    $st = db()->prepare("SELECT t.* FROM tags t INNER JOIN expense_tags et ON et.tag_id=t.id WHERE et.expense_id=? ORDER BY t.name");
    $st->execute([$expenseId]); return $st->fetchAll();
  }
  public static function attach(int $expenseId, int $tagId): void {
    $st = db()->prepare("INSERT IGNORE INTO expense_tags (expense_id,tag_id) VALUES (?,?)");
    $st->execute([$expenseId,$tagId]);
  }
  public static function sync(int $expenseId, array $tagIds): void {
    $st = db()->prepare("SELECT tag_id FROM expense_tags WHERE expense_id=?");
    $st->execute([$expenseId]);
    $cur = array_map('intval', array_column($st->fetchAll(),'tag_id'));
    $toAdd = array_diff($tagIds, $cur);
    $toDel = array_diff($cur, $tagIds);
    if ($toAdd) {
      $ins = db()->prepare("INSERT IGNORE INTO expense_tags (expense_id,tag_id) VALUES (?,?)");
      foreach ($toAdd as $tid) $ins->execute([$expenseId, (int)$tid]);
    }
    if ($toDel) {
      $in = implode(',', array_fill(0, count($toDel), '?'));
      $del = db()->prepare("DELETE FROM expense_tags WHERE expense_id=? AND tag_id IN ($in)");
      $i=1; $del->bindValue($i, $expenseId, PDO::PARAM_INT); $i++;
      foreach ($toDel as $tid) { $del->bindValue($i, (int)$tid, PDO::PARAM_INT); $i++; }
      $del->execute();
    }
  }
}
