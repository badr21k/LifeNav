
<?php
class login extends Controller {

  private function db() { return db_connect(); }

  // GET /login
  public function index() {
    // if already logged in, bounce to lifenav
    if (isset($_SESSION['auth'])) { 
      header('Location: /lifenav'); 
      exit; 
    }
    $title = 'Sign In';
    require 'app/views/login/index.php';
  }

  // POST /login/verify
  public function verify() {
    if (isset($_SESSION['auth'])) { 
      header('Location: /lifenav'); 
      exit; 
    }

    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // basic validation
    if ($email === '' || $password === '') {
      $_SESSION['flash_error'] = 'Email and password are required.';
      header('Location: /login'); 
      exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['flash_error'] = 'Invalid email address.';
      header('Location: /login'); 
      exit;
    }

    $dbh = $this->db();
    $stmt = $dbh->prepare("SELECT u.*, t.name as tenant_name 
                          FROM users u 
                          JOIN tenants t ON u.tenant_id = t.id 
                          WHERE u.email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
      $_SESSION['flash_error'] = 'Invalid email or password.';
      header('Location: /login'); 
      exit;
    }

    // log the user in
    $_SESSION['auth'] = [
      'id' => (int)$user['id'],
      'tenant_id' => (int)$user['tenant_id'],
      'name' => $user['name'],
      'email' => $user['email'],
      'role' => $user['role']
    ];

    header('Location: /lifenav'); 
    exit;
  }
}
