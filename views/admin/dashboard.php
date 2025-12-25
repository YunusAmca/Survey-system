<?php
require_once __DIR__ . '/../../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            <a href="<?php echo $base_url; ?>/admin/dashboard" class="active">Dashboard</a>
            <a href="<?php echo $base_url; ?>/admin/surveys">Manage Surveys</a>
            <a href="<?php echo $base_url; ?>/admin/users">Users</a>
            <a href="<?php echo $base_url; ?>/api/auth.php?action=logout"
                style="margin-top:auto; color: var(--danger-color);">Logout</a>
        </div>
        <div class="main-content">
            <h1 class="mb-4">Dashboard</h1>
            <div class="dashboard-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <div class="card">
                    <h3>Surveys</h3>
                    <p style="color: var(--text-muted); margin-bottom: 24px;">Manage surveys (Create, Edit, Delete)</p>
                    <a href="<?php echo $base_url; ?>/admin/surveys" class="btn">Go to Surveys</a>
                </div>
                <div class="card">
                    <h3>Users</h3>
                    <p style="color: var(--text-muted); margin-bottom: 24px;">View registered users and their activity
                    </p>
                    <button class="btn btn-secondary" disabled>Coming Soon</button>
                </div>
                <div class="card">
                    <h3>Statistics</h3>
                    <p style="color: var(--text-muted); margin-bottom: 24px;">View overall response rates and data</p>
                    <button class="btn btn-secondary" disabled>Coming Soon</button>
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
        // Mobile Sidebar Toggle
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 &&
                    !sidebar.contains(e.target) &&
                    !toggleBtn.contains(e.target) &&
                    sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            });
        }
    </script>

</body>

</html>