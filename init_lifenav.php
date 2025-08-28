
<?php
require_once 'app/init.php';

echo "Initializing LifeNav database...\n";

$dbh = db();

// Read and execute schema
$schema = file_get_contents('app/database_schema.sql');
$statements = array_filter(array_map('trim', explode(';', $schema)));

foreach ($statements as $sql) {
    if (!empty($sql)) {
        try {
            $dbh->exec($sql);
            echo "✓ Executed: " . substr($sql, 0, 50) . "...\n";
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            echo "SQL: " . substr($sql, 0, 100) . "...\n";
        }
    }
}

echo "\nLifeNav database initialization complete!\n";
echo "You can now access LifeNav at /lifenav\n";
