
<?php

function db(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = DB_HOST;
        $dbname = DB_DATABASE;
        $username = DB_USER;
        $password = DB_PASS;
        $port = DB_PORT;
        
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
    }
    
    return $pdo;
}
