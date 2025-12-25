<?php
// config/db.php - Railway/Production Compatible

// Use Railway's PostgreSQL environment variables (PGHOST, PGDATABASE, etc.)
// Falls back to local defaults for development
$host = getenv('PGHOST') ?: getenv('DB_HOST') ?: 'caboose.proxy.rlwy.net';
$db = getenv('PGDATABASE') ?: getenv('DB_NAME') ?: 'servern';
$user = getenv('PGUSER') ?: getenv('DB_USER') ?: 'postgres';
$pass = getenv('PGPASSWORD') ?: getenv('DB_PASS') ?: 'XnybzVcIEWVXgXfAAVjIlaJyGUKQaaQT';
$port = getenv('PGPORT') ?: getenv('DB_PORT') ?: '46735';

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
