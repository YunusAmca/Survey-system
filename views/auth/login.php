<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Survey System</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>

<body>

    <div class="auth-container">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 24px;">Login</h2>
            <div id="error-msg" class="alert"
                style="display:none; color: var(--danger-color); background: rgba(231, 76, 60, 0.1); border: 1px solid var(--danger-color);">
            </div>
            <form id="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn" style="width: 100%; justify-content: center;">Login</button>
            </form>
            <div style="margin-top: 16px; text-align: center;">
                <a href="<?php echo $base_url; ?>/register" class="toggle-link">Don't have an account?
                    <strong>Register</strong></a>
            </div>
        </div>
    </div>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode">
        🌙
    </button>
    <script src="<?php echo $base_url; ?>/assets/js/theme.js"></script>

    <script>
        document.getElementById('login-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('date_time', new Date().toISOString()); // Additional metadata if needed

            fetch('<?php echo $base_url; ?>/api/auth.php?action=login', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        const errorDiv = document.getElementById('error-msg');
                        errorDiv.textContent = data.message;
                        errorDiv.style.display = 'block';
                    }
                })
                .catch(err => console.error(err));
        });
    </script>

</body>

</html>