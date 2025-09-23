<?php
// Simple CSRF utilities

function csrf_token(): string {
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_token" value="' . $t . '">';
}

function csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return; // only verify on POST
    $sent = (string)($_POST['_token'] ?? '');
    $valid = is_string($sent) && $sent !== '' && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);
    // rotate token regardless
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    if (!$valid) {
        http_response_code(400);
        $_SESSION['flash_error'] = 'Security verification failed. Please try again.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/login'));
        exit;
    }
}

// Verify CSRF via header for JSON APIs (e.g., X-CSRF-Token)
function csrf_verify_header(string $headerName = 'X-CSRF-Token'): void {
    // Accept only for state-changing verbs
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['POST','PUT','PATCH','DELETE'], true)) return;
    $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
    $sent = (string)($_SERVER[$headerKey] ?? '');
    $valid = is_string($sent) && $sent !== '' && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);
    // rotate token regardless
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    if (!$valid) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}
