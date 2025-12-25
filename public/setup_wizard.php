<?php
// public/setup_wizard.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🛠️ System Setup & Troubleshoot</h1>";

// 1. Read Config
echo "<h3>1. Checking Configuration</h3>";
$config_path = __DIR__ . '/../config/db.php';
if (!file_exists($config_path)) {
    die("❌ config/db.php not found.");
}
require $config_path;
echo "✅ Config loaded. User: <code>$user</code>, DB: <code>$db</code><br>";

// 2. Connect to Server (Generic)
echo "<h3>2. Connecting to PostgreSQL Server</h3>";
try {
    // Connect to 'postgres' system db first to check credentials
    $dsn_gen = "pgsql:host=$host;port=$port;dbname=postgres;";
    $pdo_gen = new PDO($dsn_gen, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Connected to Postgres Server successfully.<br>";
} catch (PDOException $e) {
    die("<div style='background:#fdd; padding:10px;'>❌ <b>Auth Failed:</b> Could not connect to PostgreSQL server.<br>Error: " . $e->getMessage() . "<br><br>👉 Check your password in <code>config/db.php</code>.</div>");
}

// 3. Check/Create Database
echo "<h3>3. Checking Database '$db'</h3>";
$stmt = $pdo_gen->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
$stmt->execute([$db]);
$exists = $stmt->fetchColumn();

if ($exists) {
    echo "✅ Database '$db' exists.<br>";
} else {
    echo "⚠️ Database '$db' does NOT exist. Attempting to create...<br>";
    try {
        $pdo_gen->exec("CREATE DATABASE \"$db\";");
        echo "✅ Database '$db' created successfully.<br>";
    } catch (PDOException $e) {
        die("❌ Failed to create database: " . $e->getMessage());
    }
}

// 4. Connect to Specific DB
echo "<h3>4. Connecting to '$db'</h3>";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Connected to '$db'.<br>";
} catch (PDOException $e) {
    die("❌ Failed to connect to '$db' even though it exists: " . $e->getMessage());
}

// 5. Check Tables
echo "<h3>5. Checking Tables</h3>";
$required_tables = ['users', 'surveys', 'questions', 'submissions', 'answers'];
$found_tables = [];

$stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
while ($row = $stmt->fetch()) {
    $found_tables[] = $row['table_name'];
}

$missing = array_diff($required_tables, $found_tables);

if (empty($missing)) {
    echo "<p style='color:green'>✅ All tables found: " . implode(', ', $found_tables) . "</p>";
    echo "<div style='background:#dfd; padding:20px; font-size:1.2em;'>🎉 System is Ready! <a href='index.php'>Go to App</a></div>";
} else {
    echo "<p style='color:red'>❌ Missing tables: " . implode(', ', $missing) . "</p>";
    echo "<form method='post'><button type='submit' name='install' style='padding:10px; font-size:1.2em; cursor:pointer;'>🛠️ Install Missing Tables Now</button></form>";
}

// Handle Install
if (isset($_POST['install'])) {
    echo "<hr><h3>Installing Tables...</h3>";
    try {
        $sql = file_get_contents(__DIR__ . '/../setup.sql');
        $pdo->exec($sql);
        echo "<p style='color:green'>✅ Tables created successfully! Refresh page to verify.</p>";
        echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>❌ Error running SQL: " . $e->getMessage() . "</p>";
    }
}
?>