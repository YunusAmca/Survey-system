<?php
// public/install.php
require_once __DIR__ . '/../config/db.php';

try {
    $sql = file_get_contents(__DIR__ . '/../setup.sql');
    $pdo->exec($sql);
    echo "Database tables created successfully! Please delete this file and <a href='login'>Login</a>.";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>