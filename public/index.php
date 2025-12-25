<?php
// public/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/db.php';

// Simple Router
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// Normalize base path (remove backslashes on Windows)
$base_path = str_replace('\\', '/', $base_path);
if ($base_path === '/' || $base_path === '.') {
    $base_path = '';
}

$path = parse_url($request_uri, PHP_URL_PATH);

// Remove base path from request path to get relative route
if (!empty($base_path) && strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}

// Make base_url available to views
$base_url = $base_path;

// Remove query string and trailing slash
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/';
}

// Routes
switch ($path) {
    case '/':
    case '/login':
        require __DIR__ . '/../views/auth/login.php';
        break;
    case '/register':
        require __DIR__ . '/../views/auth/register.php';
        break;
    case '/admin':
    case '/admin/dashboard':
        require __DIR__ . '/../views/admin/dashboard.php';
        break;
    case '/user/dashboard':
        require __DIR__ . '/../views/user/dashboard.php';
        break;
    case '/admin/surveys':
        require __DIR__ . '/../views/admin/surveys.php'; // Manage surveys
        break;
    case '/admin/survey-edit':
        require __DIR__ . '/../views/admin/survey_edit.php';
        break;
    case '/admin/results':
        require __DIR__ . '/../views/admin/results.php';
        break;
    case '/admin/submission':
        require __DIR__ . '/../views/admin/submission_view.php';
        break;
    case '/admin/users':
        require __DIR__ . '/../views/admin/users.php'; // Need to create this
        break;
    // ... add more routes
    default:
        // Check if it's a survey link
        if (preg_match('#^/s/([a-zA-Z0-9]+)$#', $path, $matches)) {
            $survey_link = $matches[1];
            require __DIR__ . '/../views/user/survey_view.php';
        } else {
            // 404
            http_response_code(404);
            echo "404 Not Found";
        }
        break;
}
?>