<?php
require_once __DIR__ . '/../../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Surveys</title>
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
            <a href="<?php echo $base_url; ?>/admin/surveys" class="active">Manage Surveys</a>
            <a href="<?php echo $base_url; ?>/admin/users">Users</a>
            <a href="<?php echo $base_url; ?>/api/auth.php?action=logout"
                style="margin-top:auto; color: var(--danger-color);">Logout</a>
        </div>
        <div class="main-content">
            <div class="flex-between mb-4">
                <h1>Manage Surveys</h1>
                <button class="btn" onclick="openModal()">+ Create New Survey</button>
            </div>

            <div id="survey-list" style="display: grid; gap: 16px;">
                <!-- Surveys will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Create Survey Modal -->
    <div id="create-modal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div class="card" style="width: 100%; max-width: 400px; margin: 20px;">
            <h2 class="mb-4">Create Survey</h2>
            <form id="create-survey-form">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required placeholder="e.g., Customer Satisfaction">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Brief description...">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode">
        🌙
    </button>
    <script src="<?php echo $base_url; ?>/assets/js/theme.js"></script>

    <script>
        // Mobile Sidebar Logic
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            });
        }

        // Modal Logic
        const modal = document.getElementById('create-modal');
        function openModal() { modal.style.display = 'flex'; }
        function closeModal() { modal.style.display = 'none'; }

        function loadSurveys() {
            fetch('<?php echo $base_url; ?>/api/surveys.php')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('survey-list');
                    list.innerHTML = '';
                    if (data.success && data.surveys) {
                        data.surveys.forEach(s => {
                            const surveyLink = `<?php echo $base_url; ?>/s/${s.unique_link}`; // Relative for link
                            const fullLink = `${window.location.origin}${surveyLink}`; // Full for clipboard

                            const div = document.createElement('div');
                            div.className = 'card flex-between';
                            div.style.flexWrap = 'wrap';
                            div.style.gap = '16px';

                            div.innerHTML = `
                            <div style="flex:1; min-width: 200px;">
                                <strong style="font-size:1.1rem;">${s.title}</strong><br>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                    <small style="color:var(--text-muted); background:var(--bg-color); padding:4px 8px; border-radius:4px; font-family:monospace;">/s/${s.unique_link}</small>
                                    <button class="btn btn-secondary btn-icon" style="width:24px; height:24px; font-size:12px; padding:0;" 
                                        onclick="Utils.copyToClipboard('${fullLink}', this)" title="Copy Link">📋</button>
                                </div>
                            </div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                <button class="btn" style="background:var(--success-color);" onclick="window.location.href='<?php echo $base_url; ?>/admin/results?id=${s.id}'">Results</button>
                                <button class="btn" onclick="window.location.href='<?php echo $base_url; ?>/admin/survey-edit?id=${s.id}'">Edit Questions</button>
                                <button class="btn btn-delete" onclick="deleteSurvey(${s.id})">Delete</button>
                            </div>
                        `;
                            list.appendChild(div);
                        });
                    }
                });
        }

        function deleteSurvey(id) {
            if (!confirm('Are you sure you want to delete this survey?')) return;
            fetch('<?php echo $base_url; ?>/api/surveys.php?id=' + id, { method: 'DELETE' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) loadSurveys();
                    else alert('Error deleting');
                });
        }

        document.getElementById('create-survey-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('<?php echo $base_url; ?>/api/surveys.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadSurveys();
                        this.reset();
                    } else {
                        alert(data.message);
                    }
                });
        });

        loadSurveys();
    </script>

</body>

</html>