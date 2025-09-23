<?php
// Lightweight router for PHP built-in server to mimic .htaccess rewrites
// Routes all non-existent file requests to index.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = __DIR__ . $uri;

// If the requested path is a real file (asset), let the server handle it
if ($uri !== '/' && file_exists($path) && is_file($path)) {
    return false;
}

// Otherwise, route to front controller
require __DIR__ . '/index.php';
