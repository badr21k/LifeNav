<?php
function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function csrf_field(): string {
  return '<input type="hidden" name="_token" value="'.htmlspecialchars(csrf_token(),ENT_QUOTES).'">';
}
function csrf_verify(): void {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  $t = $_POST['_token'] ?? '';
  if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
    http_response_code(419);
    echo 'Invalid CSRF token'; exit;
  }
}
