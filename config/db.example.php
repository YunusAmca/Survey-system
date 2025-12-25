<?php
// config/db.php
// Copy this file to db.php and fill in your database credentials

$host = 'localhost';
$db = 'survey_system';
$user = 'your_username';
$pass = 'your_password';
$port = "5432";

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
?>