<?php
// public/api/auth.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $passcode = $_POST['passcode'] ?? '';

        // Basic Validation
        if (empty($fullname) || empty($email) || empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        // Admin Passcode Check
        if ($role === 'admin') {
            if ($passcode !== '19popcorn!') {
                echo json_encode(['success' => false, 'message' => 'Invalid Admin Passcode.']);
                exit;
            }
        } else {
            $role = 'user'; // Force user role if not admin
        }

        // Hash Password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $username, $password_hash, $role]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'unique constraint') !== false) {
                echo json_encode(['success' => false, 'message' => 'Username or Email already exists.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        }
    } elseif ($action === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and Password required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                $redirect = ($user['role'] === 'admin') ? '/admin/dashboard' : '/user/dashboard'; // Or a home page
                // Note: User dashboard in plan was just viewing list of filled surveys, or maybe just a message.
                // Requirement: "Girişdən sonra yönləndirmə: İstifadəçi → anket doldurma səhifəsi" (User -> survey filling page). 
                // But normally user lands on a dashboard or just waits for a link? 
                // Creating a simplified user dashboard for now.
                if ($user['role'] === 'user') {
                    $redirect = '/user/dashboard';
                }

                echo json_encode(['success' => true, 'redirect' => $redirect]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error/']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} else {
    // Handle GET for logout
    if ($action === 'logout') {
        session_destroy();
        header('Location: ../login');
        exit;
    }
}
?>