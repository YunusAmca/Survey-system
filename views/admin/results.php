<?php
// views/admin/results.php
require_once __DIR__ . '/../../includes/auth_check.php';
$survey_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey)
    die("Survey not found");

$stmt = $pdo->prepare("
    SELECT sub.id, sub.submitted_at, u.username, u.full_name
    FROM submissions sub
    LEFT JOIN users u ON sub.user_id = u.id
    WHERE sub.survey_id = ?
    ORDER BY sub.submitted_at DESC
");
$stmt->execute([$survey_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - <?php echo htmlspecialchars($survey['title']); ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <style>
        .submission-row {
            cursor: pointer;
            transition: background 0.2s;
        }

        .submission-row:hover {
            background-color: var(--bg-color);
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
                    <a href="<?php echo $base_url; ?>/admin/surveys"
                        style="color:var(--text-muted); font-size:0.9rem;">&larr; Back to Surveys</a>
                    <h1 style="margin-top:8px;">Results: <?php echo htmlspecialchars($survey['title']); ?></h1>
                </div>
            </div>

            <div class="card">
                <div class="flex-between mb-4">
                    <h3>Submissions</h3>
                    <span class="badge"
                        style="background:var(--primary-color); color:#fff;"><?php echo count($submissions); ?>
                        Total</span>
                </div>

                <?php if (count($submissions) === 0): ?>
                    <p style="text-align:center; color:var(--text-muted); padding:20px;">No answer yet.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th style="text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $sub): ?>
                                    <tr class="submission-row"
                                        onclick="window.location.href='<?php echo $base_url; ?>/admin/submission?id=<?php echo $sub['id']; ?>'">
                                        <td>
                                            <div style="font-weight:600;">
                                                <?php echo htmlspecialchars($sub['username'] ?? 'Anonymous'); ?></div>
                                            <div style="font-size:0.85rem; color:var(--text-muted);">
                                                <?php echo htmlspecialchars($sub['full_name'] ?? '-'); ?></div>
                                        </td>
                                        <td><?php echo date('M j, Y, g:i a', strtotime($sub['submitted_at'])); ?></td>
                                        <td style="text-align:right;">
                                            <a href="<?php echo $base_url; ?>/admin/submission?id=<?php echo $sub['id']; ?>"
                                                class="btn btn-secondary" style="padding:4px 12px; font-size:0.85rem;">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
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