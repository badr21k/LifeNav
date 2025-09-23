<?php
/* database connection */
function db_connect() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_DATABASE . ';charset=utf8mb4';
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        // If SSL CA is provided (e.g., for TiDB Cloud public endpoint), enable TLS
        if (defined('DB_SSL_CA') && DB_SSL_CA) {
            // These constants are only defined for the pdo_mysql driver
            if (defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $opts[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
            }
            // Optional: verify server cert when CA is provided (supported on some PHP builds)
            if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; // set true if you want strict verification
            }
        }
        return new PDO($dsn, DB_USER, DB_PASS, $opts);
    } catch (PDOException $e) {
        http_response_code(500);
        echo "<h1>DB connection failed</h1><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        exit;
    }
}
