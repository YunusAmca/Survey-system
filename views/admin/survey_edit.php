<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$survey_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Survey</title>
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
        .question-item {
            background: var(--surface-color);
            padding: 20px;
            margin-bottom: 16px;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s;
        }

        .question-item:hover {
            transform: translateX(4px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--surface-color);
            width: 90%;
            max-width: 500px;
            padding: 30px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .options-container {
            margin-top: 10px;
            padding: 15px;
            background: var(--bg-color);
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-sm);
        }

        .option-input {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
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
                    <h1 style="margin-top:8px;">Edit Questions</h1>
                </div>
                <button class="btn" onclick="openAddModal()">+ Add Question</button>
            </div>

            <div id="questions-list">
                <!-- Questions loaded here -->
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div id="add-modal" class="modal">
        <div class="modal-content">
            <h2 class="mb-4">Add Question</h2>
            <form id="add-question-form">
                <input type="hidden" name="survey_id" value="<?php echo htmlspecialchars($survey_id); ?>">
                <div class="form-group">
                    <label>Question Text</label>
                    <input type="text" name="question_text" required placeholder="What is your question?">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="question_type" id="q-type">
                        <option value="text">Text (Open Answer)</option>
                        <option value="choice">Multiple Choice</option>
                        <option value="yes_no">Yes / No</option>
                        <option value="range">Range (Slider)</option>
                    </select>
                </div>

                <!-- Dynamic Options Section -->
                <div id="options-section" style="display:none;">
                    <label>Options</label>
                    <div id="options-container" class="options-container">
                        <!-- Option inputs go here -->
                    </div>
                    <button type="button" class="btn btn-secondary"
                        style="width:100%; margin-top:10px; font-size:0.9rem;" onclick="addOptionInput()">+ Add
                        Option</button>
                </div>

                <!-- Range Config -->
                <div id="range-section" style="display:none;">
                    <label>Range Config</label>
                    <div style="display:flex; gap:10px;">
                        <input type="number" id="range-min" placeholder="Min (0)">
                        <input type="number" id="range-max" placeholder="Max (100)">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn">Save Question</button>
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
        // Sidebar
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn) toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));

        const surveyId = "<?php echo $survey_id; ?>";
        const typeSelect = document.getElementById('q-type');
        const optionsSection = document.getElementById('options-section');
        const rangeSection = document.getElementById('range-section');
        const optionsContainer = document.getElementById('options-container');

        typeSelect.addEventListener('change', function () {
            optionsSection.style.display = 'none';
            rangeSection.style.display = 'none';
            if (this.value === 'choice') {
                optionsSection.style.display = 'block';
                if (optionsContainer.children.length === 0) addOptionInput();
            } else if (this.value === 'range') {
                rangeSection.style.display = 'block';
            }
        });

        function addOptionInput() {
            const div = document.createElement('div');
            div.className = 'option-input';
            div.innerHTML = `
            <input type="text" placeholder="Option text" class="opt-val">
            <button type="button" onclick="this.parentElement.remove()" class="btn btn-delete btn-icon" style="width:40px; height:40px; padding:0;">✕</button>
        `;
            optionsContainer.appendChild(div);
        }

        const modal = document.getElementById('add-modal');
        function openAddModal() { modal.classList.add('active'); }
        function closeAddModal() { modal.classList.remove('active'); }

        function loadQuestions() {
            fetch('<?php echo $base_url; ?>/api/questions.php?survey_id=' + surveyId)
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('questions-list');
                    list.innerHTML = '';
                    if (data.success && data.questions) {
                        data.questions.forEach(q => {
                            const div = document.createElement('div');
                            div.className = 'question-item';
                            let details = `<span class="badge">${q.question_type}</span>`;
                            if (q.question_type === 'choice') {
                                const opts = JSON.parse(q.options || '[]');
                                details += ` <span style="color:var(--text-muted); font-size:0.9rem;">(${opts.join(', ')})</span>`;
                            }
                            div.innerHTML = `
                            <div class="flex-between">
                                <div>
                                    <strong style="font-size:1.1rem;">${q.question_text}</strong><br>
                                    <div style="margin-top:4px;">${details}</div>
                                </div>
                                <button class="btn btn-delete" onclick="deleteQuestion(${q.id})">Delete</button>
                            </div>
                        `;
                            list.appendChild(div);
                        });
                    }
                });
        }

        function deleteQuestion(id) {
            if (!confirm('Delete this question?')) return;
            fetch('<?php echo $base_url; ?>/api/questions.php?id=' + id, { method: 'DELETE' })
                .then(r => r.json())
                .then(data => { loadQuestions(); });
        }

        document.getElementById('add-question-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const type = document.getElementById('q-type').value;

            let options = [];
            if (type === 'choice') {
                document.querySelectorAll('.opt-val').forEach(inp => {
                    if (inp.value.trim()) options.push(inp.value.trim());
                });
            } else if (type === 'range') {
                const min = document.getElementById('range-min').value || 0;
                const max = document.getElementById('range-max').value || 100;
                options = { min, max };
            }

            formData.append('options', JSON.stringify(options));

            fetch('<?php echo $base_url; ?>/api/questions.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeAddModal();
                        loadQuestions();
                        this.reset();
                        document.getElementById('options-container').innerHTML = '';
                    } else {
                        console.error('Add question failed:', data.message);
                        alert(data.message);
                    }
                });
        });

        loadQuestions();
    </script>

</body>

</html>