<?php
// public/api/surveys.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List all surveys
    try {
        $stmt = $pdo->query("SELECT * FROM surveys ORDER BY created_at DESC");
        $surveys = $stmt->fetchAll();
        echo json_encode(['success' => true, 'surveys' => $surveys]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error']);
    }
} elseif ($method === 'POST') {
    // Create survey
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title required']);
        exit;
    }

    // Generate Unique Link
    $unique_link = bin2hex(random_bytes(8));

    try {
        $stmt = $pdo->prepare("INSERT INTO surveys (title, description, creator_id, unique_link) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $_SESSION['user_id'], $unique_link]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    // Delete survey
    $id = $_GET['id'] ?? 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM surveys WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error']);
    }
}
?>