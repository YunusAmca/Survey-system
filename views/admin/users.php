<?php
// views/admin/users.php
require_once __DIR__ . '/../../includes/auth_check.php';

$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name, u.email, u.role, u.created_at, COUNT(s.id) as submission_count
    FROM users u
    LEFT JOIN submissions s ON u.id = s.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
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

    <div class="admin-layout">
        <!-- Mobile Header -->
        <div class="mobile-header hide-desktop">
            <h3>Admin Panel</h3>
            <button class="btn btn-secondary btn-icon" id="sidebar-toggle">☰</button>
        </div>

        <div class="sidebar" id="sidebar">
            <h3>Admin Panel</h3>
            <a href="<?php echo $base_url; ?>/admin/dashboard">Dashboard</a>
            <a href="<?php echo $base_url; ?>/admin/surveys">Manage Surveys</a>
            <a href="<?php echo $base_url; ?>/admin/users" class="active">Users</a>
            <a href="<?php echo $base_url; ?>/api/auth.php?action=logout"
                style="margin-top:auto; color: var(--danger-color);">Logout</a>
        </div>

        <div class="main-content">
            <h1 class="mb-4">Registered Users</h1>

            <div class="card">
                <div class="flex-between mb-4">
                    <h3>All Users</h3>
                    <span class="badge"
                        style="background:var(--primary-color); color:#fff;"><?php echo count($users); ?> Total</span>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Name / Email</th>
                                <th>Role</th>
                                <th>Surveys Filled</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600;"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                        <div style="font-size:0.85rem; color:var(--text-muted);">
                                            <?php echo htmlspecialchars($u['email']); ?>
                                            (@<?php echo htmlspecialchars($u['username']); ?>)
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge" style="background:var(--danger-color); color:#fff;">ADMIN</span>
                                        <?php else: ?>
                                            <span class="badge"
                                                style="background:var(--secondary-color); color:#fff; background-color:#3498db;">USER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight:bold;"><?php echo $u['submission_count']; ?></span>
                                    </td>
                                    <td style="color:var(--text-muted); font-size:0.9rem;">
                                        <?php echo date('M j, Y', strtotime($u['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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