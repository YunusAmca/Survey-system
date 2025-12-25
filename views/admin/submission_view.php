<?php
// views/admin/submission_view.php
require_once __DIR__ . '/../../includes/auth_check.php';
$sub_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT sub.*, s.title as survey_title, s.id as survey_id, u.username, u.full_name
    FROM submissions sub
    JOIN surveys s ON sub.survey_id = s.id
    LEFT JOIN users u ON sub.user_id = u.id
    WHERE sub.id = ?
");
$stmt->execute([$sub_id]);
$submission = $stmt->fetch();

if (!$submission)
    die("Submission not found");

$stmt = $pdo->prepare("
    SELECT a.answer_text, q.question_text, q.question_type
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    WHERE a.submission_id = ?
    ORDER BY q.order_num ASC, q.id ASC
");
$stmt->execute([$sub_id]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission #<?php echo $sub_id; ?></title>
    <script>
        (function () {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && prefersDark)) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <style>
        .answer-card {
            background: var(--surface-color);
            padding: 20px;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            margin-bottom: 16px;
            border-left: 3px solid var(--border-color);
        }

        .question-text {
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .answer-text {
            font-size: 1.1rem;
            color: var(--text-main);
            line-height: 1.5;
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <!-- Mobile Header -->
        <div class="mobile-header hide-desktop">
            <h3>Admin Panel</h3>
            <button class="btn btn-secondary btn-icon" id="sidebar-toggle">☰</button>
        </div>

        <div class="sidebar" id="sidebar">
            <h3>Admin Panel</h3>
            <a href="<?php echo $base_url; ?>/admin/dashboard">Dashboard</a>
            <a href="<?php echo $base_url; ?>/admin/surveys" class="active">Manage Surveys</a>
            <a href="<?php echo $base_url; ?>/admin/users">Users</a>
            <a href="<?php echo $base_url; ?>/api/auth.php?action=logout"
                style="margin-top:auto; color: var(--danger-color);">Logout</a>
        </div>

        <div class="main-content">
            <div class="flex-between mb-4">
                <div>
                    <a href="<?php echo $base_url; ?>/admin/results?id=<?php echo $submission['survey_id']; ?>"
                        style="color:var(--text-muted); font-size:0.9rem;">&larr; Back to Results</a>
                    <h1 style="margin-top:8px;">Submission Details</h1>
                </div>
            </div>

            <div class="card mb-4" style="background:var(--bg-color); border:none; padding:16px;">
                <div class="flex-between">
                    <div>
                        <strong>Survey:</strong> <?php echo htmlspecialchars($submission['survey_title']); ?>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600;">
                            <?php echo htmlspecialchars($submission['username'] ?? 'Anonymous'); ?>
                        </div>
                        <div style="font-size:0.85rem; color:var(--text-muted);">
                            <?php echo date('M j, Y, g:i a', strtotime($submission['submitted_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($answers as $ans): ?>
                <div class="answer-card">
                    <div class="question-text"><?php echo htmlspecialchars($ans['question_text']); ?></div>
                    <div class="answer-text"><?php echo nl2br(htmlspecialchars($ans['answer_text'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode">
        🌙
    </button>
    <script src="<?php echo $base_url; ?>/assets/js/theme.js"></script>

    <script>
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn) toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    </script>
</body>

</html>