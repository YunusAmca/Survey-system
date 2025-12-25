<?php
require_once __DIR__ . '/../../includes/auth_check.php';

// Son 5 doldurulmuş anket
$stmt = $pdo->query("
    SELECT sub.id, sub.submitted_at, s.title as survey_title, u.username, u.full_name
    FROM submissions sub
    JOIN surveys s ON sub.survey_id = s.id
    LEFT JOIN users u ON sub.user_id = u.id
    ORDER BY sub.submitted_at DESC
    LIMIT 5
");
$recent_submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Anket statistikası - hər anket üçün cavab sayı
$stmt = $pdo->query("
    SELECT s.id, s.title, COUNT(sub.id) as submission_count
    FROM surveys s
    LEFT JOIN submissions sub ON s.id = sub.survey_id
    GROUP BY s.id, s.title
    ORDER BY submission_count DESC
");
$survey_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Range suallar üçün ortalama
$stmt = $pdo->query("
    SELECT q.id, q.question_text, q.survey_id, s.title as survey_title, 
           AVG(CAST(a.answer_text AS DECIMAL)) as average_value,
           COUNT(a.id) as answer_count
    FROM questions q
    JOIN surveys s ON q.survey_id = s.id
    JOIN answers a ON q.id = a.question_id
    WHERE q.question_type = 'range'
    GROUP BY q.id, q.question_text, q.survey_id, s.title
    HAVING COUNT(a.id) > 0
");
$range_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Choice və Yes/No suallar üçün ən çox seçilən variant
$stmt = $pdo->query("
    SELECT q.id, q.question_text, q.question_type, q.survey_id, s.title as survey_title,
           a.answer_text, COUNT(a.id) as vote_count
    FROM questions q
    JOIN surveys s ON q.survey_id = s.id
    JOIN answers a ON q.id = a.question_id
    WHERE q.question_type IN ('choice', 'yes_no')
    GROUP BY q.id, q.question_text, q.question_type, q.survey_id, s.title, a.answer_text
    ORDER BY q.id, vote_count DESC
");
$choice_stats_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Choice statistikasını qruplaşdır
$choice_stats = [];
foreach ($choice_stats_raw as $row) {
    $qid = $row['id'];
    if (!isset($choice_stats[$qid])) {
        $choice_stats[$qid] = [
            'question_text' => $row['question_text'],
            'survey_title' => $row['survey_title'],
            'question_type' => $row['question_type'],
            'options' => []
        ];
    }
    $choice_stats[$qid]['options'][] = [
        'answer' => $row['answer_text'],
        'count' => $row['vote_count']
    ];
}

// Ümumi statistika
$total_surveys = count($survey_stats);
$total_submissions = array_sum(array_column($survey_stats, 'submission_count'));
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface-color);
            padding: 20px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }
        }

        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .bar-chart {
            margin-top: 8px;
        }

        .bar-row {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            gap: 8px;
        }

        .bar-label {
            min-width: 80px;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-align: right;
        }

        .bar-container {
            flex: 1;
            height: 24px;
            background: var(--bg-color);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), hsl(var(--primary-hue), 80%, 70%));
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 30px;
        }

        .stat-section {
            margin-bottom: 24px;
        }

        .stat-section h3 {
            margin-bottom: 16px;
            font-size: 1rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .question-stat-card {
            background: var(--surface-color);
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            margin-bottom: 16px;
        }

        .question-stat-card h4 {
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .survey-badge {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: block;
        }

        .average-display {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 12px;
        }

        .average-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .average-bar {
            flex: 1;
            height: 8px;
            background: var(--bg-color);
            border-radius: 99px;
            overflow: hidden;
        }

        .average-bar-fill {
            height: 100%;
            background: var(--primary-color);
            border-radius: 99px;
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
            <a href="<?php echo $base_url; ?>/admin/dashboard" class="active">Dashboard</a>
            <a href="<?php echo $base_url; ?>/admin/surveys">Manage Surveys</a>
            <a href="<?php echo $base_url; ?>/admin/users">Users</a>
            <a href="<?php echo $base_url; ?>/api/auth.php?action=logout"
                style="margin-top:auto; color: var(--danger-color);">Logout</a>
        </div>

        <div class="main-content">
            <h1 class="mb-4">Dashboard</h1>

            <!-- Ümumi Statistika -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_surveys; ?></div>
                    <div class="stat-label">Total Surveys</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_submissions; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-label">Registered Users</div>
                </div>
            </div>

            <div class="dashboard-layout">
                <!-- Sol tərəf - Statistika -->
                <div>
                    <!-- Anket Statistikası -->
                    <div class="stat-section">
                        <h3>📊 Survey Response Statistics</h3>
                        <div class="card">
                            <?php
                            $max_count = !empty($survey_stats) ? max(array_column($survey_stats, 'submission_count')) : 1;
                            if ($max_count == 0)
                                $max_count = 1;
                            foreach ($survey_stats as $stat):
                                $percent = ($stat['submission_count'] / $max_count) * 100;
                                ?>
                                <div class="bar-row">
                                    <div class="bar-label">
                                        <?php echo htmlspecialchars(mb_substr($stat['title'], 0, 15)) . (mb_strlen($stat['title']) > 15 ? '...' : ''); ?>
                                    </div>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: <?php echo max($percent, 10); ?>%">
                                            <?php echo $stat['submission_count']; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($survey_stats)): ?>
                                <p style="color: var(--text-muted); text-align: center; padding: 20px;">No surveys yet</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Range Suallar - Ortalama -->
                    <?php if (!empty($range_stats)): ?>
                        <div class="stat-section">
                            <h3>📈 Range Questions - Averages</h3>
                            <?php foreach ($range_stats as $range): ?>
                                <div class="question-stat-card">
                                    <h4><?php echo htmlspecialchars($range['question_text']); ?></h4>
                                    <span class="survey-badge">📋 <?php echo htmlspecialchars($range['survey_title']); ?>
                                        (<?php echo $range['answer_count']; ?> responses)</span>
                                    <div class="average-display">
                                        <div class="average-value"><?php echo number_format($range['average_value'], 1); ?>
                                        </div>
                                        <div class="average-bar">
                                            <div class="average-bar-fill"
                                                style="width: <?php echo min($range['average_value'], 100); ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Choice Suallar - Ən çox seçilən -->
                    <?php if (!empty($choice_stats)): ?>
                        <div class="stat-section">
                            <h3>📋 Choice Questions - Vote Distribution</h3>
                            <?php foreach ($choice_stats as $qid => $q):
                                $total_votes = array_sum(array_column($q['options'], 'count'));
                                if ($total_votes == 0)
                                    continue;
                                ?>
                                <div class="question-stat-card">
                                    <h4><?php echo htmlspecialchars($q['question_text']); ?></h4>
                                    <span class="survey-badge">📋 <?php echo htmlspecialchars($q['survey_title']); ?></span>
                                    <div class="bar-chart">
                                        <?php foreach ($q['options'] as $opt):
                                            $percent = ($opt['count'] / $total_votes) * 100;
                                            ?>
                                            <div class="bar-row">
                                                <div class="bar-label"><?php echo htmlspecialchars($opt['answer']); ?></div>
                                                <div class="bar-container">
                                                    <div class="bar-fill" style="width: <?php echo max($percent, 10); ?>%">
                                                        <?php echo round($percent); ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sağ tərəf - Son Doldurulmuş Anketlər -->
                <div>
                    <div class="card">
                        <h3 style="margin-bottom: 16px;">🕐 Recent Submissions</h3>
                        <?php if (empty($recent_submissions)): ?>
                            <p style="color: var(--text-muted); text-align: center; padding: 20px;">No submissions yet</p>
                        <?php else: ?>
                            <?php foreach ($recent_submissions as $sub): ?>
                                <div class="recent-item">
                                    <div>
                                        <div style="font-weight: 600;">
                                            <?php echo htmlspecialchars($sub['username'] ?? 'Anonymous'); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                                            <?php echo htmlspecialchars($sub['survey_title']); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            <?php echo date('M j', strtotime($sub['submitted_at'])); ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            <?php echo date('g:i a', strtotime($sub['submitted_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card" style="margin-top: 16px;">
                        <h3 style="margin-bottom: 16px;">⚡ Quick Actions</h3>
                        <a href="<?php echo $base_url; ?>/admin/surveys" class="btn"
                            style="width: 100%; justify-content: center; margin-bottom: 8px;">
                            Manage Surveys
                        </a>
                        <a href="<?php echo $base_url; ?>/admin/users" class="btn btn-secondary"
                            style="width: 100%; justify-content: center;">
                            View Users
                        </a>
                    </div>
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