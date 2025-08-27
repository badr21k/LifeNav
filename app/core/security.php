<?php
function csrf_token(): string {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (empty($_SESSION['_token'])) $_SESSION['_token'] = bin2hex(random_bytes(32));
  return $_SESSION['_token'];
}
function csrf_field(): string {
  return '<input type="hidden" name="_token" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">';
}
function csrf_verify(): void {
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') return;
  $sent = $_POST['_token'] ?? '';
  if (!$sent || !hash_equals($_SESSION['_token'] ?? '', $sent)) {
    http_response_code(419);
    echo "<h1>Invalid CSRF token</h1>";
    exit;
  }
}
function parse_amount_to_cents(string $amount): ?int {
  $amount = trim(str_replace([',',' '], ['',''], $amount));
  if ($amount === '' || !preg_match('/^-?\d+(\.\d{1,2})?$/', $amount)) return null;
  $neg = $amount[0] === '-'; if ($neg) $amount = substr($amount, 1);
  if (strpos($amount, '.') === false) return ($neg?-1:1) * ((int)$amount * 100);
  [$w,$f] = explode('.', $amount, 2);
  $f = str_pad($f,2,'0');
  return ($neg?-1:1) * ((int)$w * 100 + (int)substr($f,0,2));
}
function money_cents(int $cents, string $cur='CAD'): string {
  $neg = $cents < 0; $cents = abs($cents);
  return ($neg?'-':'') . number_format($cents/100,2) . ' ' . $cur;
}
