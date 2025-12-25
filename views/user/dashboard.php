<?php
// views/user/dashboard.php
require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/login');
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.title, sub.submitted_at, sub.id as sub_id
    FROM submissions sub
    JOIN surveys s ON sub.survey_id = s.id
    WHERE sub.user_id = ?
    ORDER BY sub.submitted_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
</head>

<body>

    <div class="container" style="max-width:800px; margin-top:40px;">
        <div class="card">
            <div class="flex-between mb-4">
                <h1 style="margin:0;">My Dashboard</h1>
                <a href="<?php echo $base_url; ?>/api/auth.php?action=logout" class="btn btn-delete"
                    style="width:auto;">Logout</a>
            </div>

            <div class="mb-4"
                style="padding:16px; background:var(--bg-color); border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                <div style="font-weight:600;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
                <div style="font-size:0.9rem; color:var(--text-muted); margin-top:4px;">Here are the surveys you have
                    participated in.</div>
            </div>

            <h2
                style="font-size:1.2rem; border-bottom:1px solid var(--border-color); padding-bottom:10px; margin-bottom:20px;">
                Completed Surveys</h2>

            <?php if (empty($submissions)): ?>
                <div style="text-align:center; padding:40px; color:var(--text-muted);">
                    <div style="font-size:2rem; margin-bottom:10px;">📭</div>
                    <p>You haven't completed any surveys yet.</p>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($submissions as $sub): ?>
                        <div class="card"
                            style="padding:16px; box-shadow:none; border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-weight:600; font-size:1.1rem;"><?php echo htmlspecialchars($sub['title']); ?>
                                </div>
                                <div style="font-size:0.85rem; color:var(--text-muted);">Submitted on
                                    <?php echo date('M j, Y, g:i a', strtotime($sub['submitted_at'])); ?>
                                </div>
                            </div>
                            <span class="badge" style="background:var(--success-color); color:#fff;">Computted</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode">
        🌙
    </button>
    <script src="<?php echo $base_url; ?>/assets/js/theme.js"></script>

</body>

</html>