<?php
class register extends Controller {

  private function db() { return db_connect(); }

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
