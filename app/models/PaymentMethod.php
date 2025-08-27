<?php
require_once __DIR__ . '/../core/database.php';

class PaymentMethod {
  public static function allActive(): array {
    return db()->query("SELECT * FROM payment_methods WHERE active=1 ORDER BY id")->fetchAll();
  }
  public static function idByName(string $name): ?int {
    $m = ['cash'=>1,'debit'=>2,'credit'=>3,'e-transfer'=>4,'etransfer'=>4,'transfer'=>4,'other'=>5];
    $k = strtolower(trim($name));
    return $m[$k] ?? null;
  }
}
