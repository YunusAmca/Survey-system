<?php
// views/user/survey_view.php
require_once __DIR__ . '/../../config/db.php';

// Auth Check (Optional for some use cases, currently enforced)
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    $current_link = $_SERVER['REQUEST_URI'];
    header("Location: " . $base_url . "/login?redirect=" . urlencode($current_link));
    exit;
}

if (!isset($survey_link))
    die("Survey not found.");

$stmt = $pdo->prepare("SELECT * FROM surveys WHERE unique_link = ?");
$stmt->execute([$survey_link]);
$survey = $stmt->fetch();

if (!$survey)
    die("Survey not found.");

$stmt = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ? ORDER BY order_num ASC, id ASC");
$stmt->execute([$survey['id']]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($survey['title']); ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <style>
        .survey-container {
            max-width: 800px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .progress-header {
            padding: 24px;
            background: var(--surface-color);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .progress-track {
            height: 6px;
            background: var(--border-color);
            border-radius: 99px;
            overflow: hidden;
            margin-top: 12px;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            width: 0%;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .survey-content {
            flex: 1;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .question-card {
            background: var(--surface-color);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .question-title {
            font-size: 1.5rem;
            margin-bottom: 24px;
            color: var(--text-main);
            font-weight: 600;
        }

        .option-label {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .option-label:hover {
            border-color: var(--primary-color);
            background: var(--bg-color);
        }

        .option-label input {
            margin-right: 12px;
            width: auto;
        }

        .range-slider-container {
            padding: 20px 0;
            text-align: center;
        }

        input[type="range"] {
            width: 100%;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="survey-container">
        <!-- Header -->
        <div class="progress-header">
            <div class="flex-between">
                <div>
                    <h2 style="margin:0; font-size:1.2rem;"><?php echo htmlspecialchars($survey['title']); ?></h2>
                    <small style="color:var(--text-muted);">Question <span id="q-current">1</span> of <span
                            id="q-total"><?php echo count($questions); ?></span></small>
                </div>
                <a href="<?php echo $base_url; ?>/user/dashboard" class="btn btn-secondary"
                    style="font-size:0.9rem;">Exit</a>
            </div>
            <div class="progress-track">
                <div class="progress-fill" id="progress-bar"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="survey-content">
            <form id="survey-form" style="width:100%;">
                <input type="hidden" name="survey_id" value="<?php echo $survey['id']; ?>">

                <?php foreach ($questions as $index => $q): ?>
                    <div class="question-step <?php echo $index === 0 ? '' : 'hidden'; ?>" id="step-<?php echo $index; ?>"
                        style="<?php echo $index !== 0 ? 'display:none;' : ''; ?>">
                        <div class="question-card">
                            <h3 class="question-title"><?php echo htmlspecialchars($q['question_text']); ?></h3>

                            <div class="answer-area">
                                <?php
                                $name = "answers[{$q['id']}]";
                                if ($q['question_type'] === 'text') {
                                    echo "<textarea name='$name' rows='4' placeholder='Type your answer here...' required></textarea>";
                                } elseif ($q['question_type'] === 'yes_no') {
                                    echo "<label class='option-label'><input type='radio' name='$name' value='Yes' required> Yes</label>";
                                    echo "<label class='option-label'><input type='radio' name='$name' value='No'> No</label>";
                                } elseif ($q['question_type'] === 'choice') {
                                    $opts = json_decode($q['options'], true);
                                    if (is_array($opts)) {
                                        foreach ($opts as $opt) {
                                            echo "<label class='option-label'><input type='radio' name='$name' value='" . htmlspecialchars($opt) . "' required> " . htmlspecialchars($opt) . "</label>";
                                        }
                                    }
                                } elseif ($q['question_type'] === 'range') {
                                    $opts = json_decode($q['options'], true);
                                    $min = $opts['min'] ?? 0;
                                    $max = $opts['max'] ?? 100;
                                    echo "<div class='range-slider-container'>";
                                    echo "<input type='range' name='$name' min='$min' max='$max' oninput='this.nextElementSibling.value = this.value'>";
                                    echo "<output style='display:block; margin-top:10px; font-weight:bold; font-size:1.2rem; color:var(--primary-color);'>" . ($min + ($max - $min) / 2) . "</output>";
                                    echo "<div class='flex-between' style='margin-top:5px; color:var(--text-muted); font-size:0.8rem;'><span>$min</span><span>$max</span></div>";
                                    echo "</div>";
                                }
                                ?>
                            </div>

                            <div style="margin-top: 32px; display:flex; justify-content:space-between;">
                                <button type="button" id="prev-btn-<?php echo $index; ?>" class="btn btn-secondary"
                                    style="<?php echo $index === 0 ? 'visibility:hidden;' : ''; ?>"
                                    onclick="changeStep(-1)">Back</button>

                                <?php if ($index === count($questions) - 1): ?>
                                    <button type="submit" class="btn" style="background:var(--success-color);">Submit
                                        Survey</button>
                                <?php else: ?>
                                    <button type="button" class="btn" onclick="changeStep(1)">Next</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>
    </div>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode">
        🌙
    </button>
    <script src="<?php echo $base_url; ?>/assets/js/theme.js"></script>

    <script>
        let currentStep = 0;
        const totalSteps = <?php echo count($questions); ?>;

        function updateProgress() {
            const percent = ((currentStep + 1) / totalSteps) * 100;
            document.getElementById('progress-bar').style.width = percent + '%';
            document.getElementById('q-current').textContent = currentStep + 1;
        }

        function changeStep(dir) {
            // Validation Logic could go here (checkValidity)
            const currentDiv = document.getElementById('step-' + currentStep);

            // Basic validation for Next
            if (dir === 1) {
                const inputs = currentDiv.querySelectorAll('input, textarea');
                let filled = false;
                inputs.forEach(input => {
                    if (input.type === 'radio') {
                        if (currentDiv.querySelector(`input[name="${input.name}"]:checked`)) filled = true;
                    } else {
                        if (input.value.trim()) filled = true;
                    }
                });

                // Allow empty for optional, but we marked mostly required in PHP
                const required = currentDiv.querySelector('[required]');
                if (required && !filled) {
                    alert('Please answer the question to proceed.');
                    return;
                }
            }

            const nextStep = currentStep + dir;
            if (nextStep >= 0 && nextStep < totalSteps) {
                document.getElementById('step-' + currentStep).style.display = 'none';
                document.getElementById('step-' + nextStep).style.display = 'block';
                currentStep = nextStep;
                updateProgress();
            }
        }

        document.getElementById('survey-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('<?php echo $base_url; ?>/api/submit.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Success State
                        document.querySelector('.survey-content').innerHTML = `
                            <div style="text-align:center; animation:fadeIn 0.5s;">
                                <div style="font-size:4rem; margin-bottom:20px;">🎉</div>
                                <h2>Thank You!</h2>
                                <p style="color:var(--text-muted); margin-bottom:20px;">Your response has been recorded successfully.</p>
                                <a href="<?php echo $base_url; ?>/user/dashboard" class="btn">Return to Dashboard</a>
                            </div>
                        `;
                        document.querySelector('.progress-header').style.display = 'none';
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });

        // Initialize progress
        updateProgress();
    </script>
</body>

</html>