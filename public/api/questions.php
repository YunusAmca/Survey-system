<?php
// public/api/questions.php
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
    $survey_id = $_GET['survey_id'] ?? 0;
    try {
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ? ORDER BY order_num ASC, id ASC");
        $stmt->execute([$survey_id]);
        $questions = $stmt->fetchAll();
        echo json_encode(['success' => true, 'questions' => $questions]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error']);
    }
} elseif ($method === 'POST') {
    $survey_id = $_POST['survey_id'] ?? 0;
    $text = $_POST['question_text'] ?? '';
    $type = $_POST['question_type'] ?? 'text';
    $options_json = $_POST['options'] ?? '[]'; // JSON string from frontend

    if (empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Question text required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO questions (survey_id, question_text, question_type, options) VALUES (?, ?, ?, ?::jsonb)");
        $stmt->execute([$survey_id, $text, $type, $options_json]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error']);
    }
}
?>