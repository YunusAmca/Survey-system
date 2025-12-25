<?php
// public/api/submit.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_id = $_POST['survey_id'];
    $answers = $_POST['answers'] ?? []; // Array [question_id => key/value]

    try {
        $pdo->beginTransaction();

        // Create Submission
        $stmt = $pdo->prepare("INSERT INTO submissions (survey_id, user_id) VALUES (?, ?) RETURNING id");
        $stmt->execute([$survey_id, $_SESSION['user_id']]);
        $submission_id = $stmt->fetchColumn();

        // Save Answers
        $stmtInsert = $pdo->prepare("INSERT INTO answers (submission_id, question_id, answer_text) VALUES (?, ?, ?)");

        foreach ($answers as $q_id => $ans_text) {
            $stmtInsert->execute([$submission_id, $q_id, $ans_text]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>