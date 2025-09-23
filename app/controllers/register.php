<?php
class register extends Controller {

  private function db() { return db_connect(); }

  // Simple column existence check
  private function hasColumn(PDO $dbh, string $table, string $column): bool {
    try {
      $st = $dbh->query("SHOW COLUMNS FROM `{$table}` LIKE " . $dbh->quote($column));
      return (bool)$st->fetch();
    } catch (Throwable $e) { return false; }
  }

  // Ensure required reference data exists for the app to function (per-tenant if supported)
  private function seedReferenceData(PDO $dbh, int $tenantId): void {
    $catNames = [
      'Transportation' => ['Car Insurance','Fuel','Parking','Public Transit','Other'],
      'Accommodation'  => ['Rent','Mortgage','Utilities','Internet','Other'],
      'Travel & Entertainment' => ['Flights','Hotels','Dining','Tours','Visas','Movies','Games','Sports','Concerts','Other'],
      'Health' => ['Doctor Visits','Medications','Dental','Vision','Fitness','Other'],
    ];

    $tenantScopedCats = $this->hasColumn($dbh,'categories','tenant_id');
    $tenantScopedSubs = $this->hasColumn($dbh,'subcategories','tenant_id');
    $tenantScopedPMs  = $this->hasColumn($dbh,'payment_methods','tenant_id');

    // Categories
    if ($tenantScopedCats) {
      // Upsert-by-name for this tenant
      $sel = $dbh->prepare("SELECT id, name FROM categories WHERE tenant_id=?");
      $sel->execute([$tenantId]);
      $existing = [];
      foreach ($sel->fetchAll() as $r) { $existing[strtolower($r['name'])] = (int)$r['id']; }
      $ins = $dbh->prepare("INSERT INTO categories (tenant_id,name,active) VALUES (?,?,1)");
      foreach (array_keys($catNames) as $name) {
        if (!isset($existing[strtolower($name)])) { $ins->execute([$tenantId,$name]); }
      }
      // refresh map
      $sel->execute([$tenantId]);
      $catMap = [];
      foreach ($sel->fetchAll() as $r) { $catMap[$r['name']] = (int)$r['id']; }
    } else {
      // Global fallback
      $cnt = (int)$dbh->query("SELECT COUNT(*) FROM categories")->fetchColumn();
      if ($cnt === 0) {
        $ins = $dbh->prepare("INSERT INTO categories (name,active) VALUES (?,1)");
        foreach (array_keys($catNames) as $name) { $ins->execute([$name]); }
      }
      // map names -> ids
      $rows = $dbh->query("SELECT id,name FROM categories")->fetchAll();
      $catMap = [];
      foreach ($rows as $r) { $catMap[$r['name']] = (int)$r['id']; }
    }

    // Subcategories
    if ($tenantScopedSubs) {
      $sel = $dbh->prepare("SELECT category_id,name FROM subcategories WHERE tenant_id=?");
      $sel->execute([$tenantId]);
      $have = [];
      foreach ($sel->fetchAll() as $r) { $have[strtolower($r['category_id'].'|'.$r['name'])] = true; }
      $ins = $dbh->prepare("INSERT INTO subcategories (tenant_id,category_id,name,active) VALUES (?,?,?,1)");
      foreach ($catNames as $catName => $subs) {
        $cid = $catMap[$catName] ?? null; if (!$cid) continue;
        foreach ($subs as $n) {
          $key = strtolower($cid.'|'.$n);
          if (!isset($have[$key])) { $ins->execute([$tenantId,$cid,$n]); }
        }
      }
    } else {
      $cnt = (int)$dbh->query("SELECT COUNT(*) FROM subcategories")->fetchColumn();
      if ($cnt === 0) {
        $ins = $dbh->prepare("INSERT INTO subcategories (category_id,name,active) VALUES (?,?,1)");
        foreach ($catNames as $catName => $subs) {
          $cid = $catMap[$catName] ?? null; if (!$cid) continue;
          foreach ($subs as $n) { $ins->execute([$cid,$n]); }
        }
      }
    }

    // Payment methods
    $pmNames = ['Cash','Debit','Credit','E-Transfer','Other'];
    if ($tenantScopedPMs) {
      $sel = $dbh->prepare("SELECT name FROM payment_methods WHERE tenant_id=?");
      $sel->execute([$tenantId]);
      $have = array_flip(array_map('strtolower', array_column($sel->fetchAll(),'name')));
      $ins = $dbh->prepare("INSERT INTO payment_methods (tenant_id,name,active) VALUES (?,?,1)");
      foreach ($pmNames as $n) { if (!isset($have[strtolower($n)])) { $ins->execute([$tenantId,$n]); } }
    } else {
      $cnt = (int)$dbh->query("SELECT COUNT(*) FROM payment_methods")->fetchColumn();
      if ($cnt === 0) {
        $ins = $dbh->prepare("INSERT INTO payment_methods (name,active) VALUES (?,1)");
        foreach ($pmNames as $n) { $ins->execute([$n]); }
      }
    }
  }

  // GET /register
  public function index() {
    // public page; if already logged in, bounce to home
    if (isset($_SESSION['auth'])) { header('Location: /home'); exit; }
    $title = 'Create Account';
    require 'app/views/register/index.php';
  }

  // POST /register/store
  public function store() {
    if (isset($_SESSION['auth'])) { header('Location: /home'); exit; }
    csrf_verify();

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password_confirm'] ?? '');

    // basic validation
    if ($name === '' || $email === '' || $pass === '' || $pass2 === '') {
      $_SESSION['flash_error'] = 'All fields are required.';
      header('Location: /register'); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['flash_error'] = 'Invalid email address.';
      header('Location: /register'); exit;
    }
    if ($pass !== $pass2) {
      $_SESSION['flash_error'] = 'Passwords do not match.';
      header('Location: /register'); exit;
    }
    if (strlen($pass) < 8) {
      $_SESSION['flash_error'] = 'Password must be at least 8 characters.';
      header('Location: /register'); exit;
    }

    $dbh = $this->db();
    // check if email taken
    $st = $dbh->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    if ($st->fetch()) {
      $_SESSION['flash_error'] = 'Email is already registered. Try logging in.';
      header('Location: /register'); exit;
    }

    // create tenant + user in a transaction
    $dbh->beginTransaction();
    try {
      // create a tenant per account (clean multi-tenant separation)
      $tenantName = $name !== '' ? $name : $email;
      $dbh->prepare("INSERT INTO tenants (name) VALUES (?)")->execute([$tenantName]);
      $tenantId = (int)$dbh->lastInsertId();

      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $stmt = $dbh->prepare("INSERT INTO users (tenant_id, name, email, password_hash, role)
                             VALUES (?, ?, ?, ?, 'user')");
      $stmt->execute([$tenantId, $name, $email, $hash]);

      $userId = (int)$dbh->lastInsertId();
      $dbh->commit();

      // Seed reference data for this tenant (or global fallback)
      $this->seedReferenceData($dbh, $tenantId);

      // log the user in
      $_SESSION['auth'] = [
        'id' => $userId,
        'tenant_id' => $tenantId,
        'name' => $name,
        'email' => $email,
        'role' => 'user'
      ];
      $_SESSION['controller'] = 'home';
      $_SESSION['method'] = 'index';

      header('Location: /home'); exit;

    } catch (Throwable $e) {
      if ($dbh->inTransaction()) $dbh->rollBack();
      $_SESSION['flash_error'] = 'Sign up failed. Please try again.';
      // Optional: log $e->getMessage()
      header('Location: /register'); exit;
    }
  }
}
