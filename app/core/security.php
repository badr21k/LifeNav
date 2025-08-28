<?php
function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function csrf_field(): string {
  return '<input type="hidden" name="csrf" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">';
}
function csrf_verify(): void {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $t = $_POST['csrf'] ?? '';
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
      http_response_code(400); die('Bad CSRF');
    }
  }
}
