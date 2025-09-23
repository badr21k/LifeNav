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
        // Enforce TLS for TiDB Cloud (public endpoints require secure transport)
        $ca = (defined('DB_SSL_CA') && DB_SSL_CA) ? DB_SSL_CA : '/etc/ssl/cert.pem';
        if (defined('PDO::MYSQL_ATTR_SSL_CA')) {
            $opts[PDO::MYSQL_ATTR_SSL_CA] = $ca;
        }
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            // Use VERIFY_CA semantics (do not verify hostname at PDO level)
            // If you want strict hostname verification, set to true and ensure
            // your CA bundle validates the endpoint hostname.
            $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        return new PDO($dsn, DB_USER, DB_PASS, $opts);
    } catch (PDOException $e) {
        http_response_code(500);
        echo "<h1>DB connection failed</h1><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p>Tip: For TiDB Cloud public endpoint, ensure TLS is enabled with a valid CA bundle. Try setting DB_SSL_CA=/etc/ssl/cert.pem in your environment.</p>";
        exit;
    }
}
