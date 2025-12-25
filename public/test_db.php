<?php
// public/test_db.php
echo "<h1>Database Connection Diagnostic</h1>";

// 1. Check Drivers
echo "<h2>1. PDO Drivers</h2>";
if (class_exists('PDO')) {
    $drivers = PDO::getAvailableDrivers();
    if (in_array('pgsql', $drivers)) {
        echo "<p style='color:green'>✅ PostgreSQL Driver (pgsql) is installed.</p>";
    } else {
        echo "<p style='color:red'>❌ PostgreSQL Driver (pgsql) is NOT installed. You need to enable <code>extension=pdo_pgsql</code> in your php.ini file.</p>";
        echo "<p>Available drivers: " . implode(', ', $drivers) . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ PDO class missing.</p>";
}

// 2. Test Connection
echo "<h2>2. Connection Attempt</h2>";
require_once __DIR__ . '/../config/db.php';

try {
    echo "Attempting to connect with:<br>";
    echo "Host: $host<br>";
    echo "User: $user<br>";
    echo "Port: $port<br>";
    // Don't show password

    // Attempt connection currently in db.php
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $test_pdo = new PDO($dsn, $user, $pass);
    echo "<p style='color:green'>✅ Connection Successful to database '$db'!</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Connection Failed: " . $e->getMessage() . "</p>";

    // Try generic connection
    try {
        echo "Attempting generic connection to 'postgres' db...<br>";
        $dsn_generic = "pgsql:host=$host;port=$port;dbname=postgres;";
        $pdo_generic = new PDO($dsn_generic, $user, $pass);
        echo "<p style='color:orange'>⚠️ Could connect to 'postgres' default database, but not '$db'. This means the user/password is correct, but the specific database is missing or you don't have permissions.</p>";
    } catch (PDOException $e2) {
        echo "<p style='color:red'>❌ Generic Connection also failed: " . $e2->getMessage() . "</p>";
    }
}
?>