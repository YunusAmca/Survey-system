<?php
require_once __DIR__ . '/config/db.php';

try {
    $sql = file_get_contents(__DIR__ . '/setup.sql');
    $pdo->exec($sql);
    echo "Database tables created successfully using setup.sql!";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>