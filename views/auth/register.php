<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Survey System</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>

<body>

    <div class="auth-container">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 24px;">Register</h2>
            <div id="error-msg" class="alert"
                style="display:none; color: var(--danger-color); background: rgba(231, 76, 60, 0.1); border: 1px solid var(--danger-color);">
            </div>
            <form id="register-form">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Choose a username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Strong password">
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group" id="passcode-group" style="display:none;">
                    <label for="passcode">Admin Passcode</label>
                    <input type="password" id="passcode" name="passcode" placeholder="Enter secret code">
                </div>

                <button type="submit" class="btn" style="width: 100%; justify-content: center;">Register</button>
            </form>
            <div style="margin-top: 16px; text-align: center;">
                <a href="<?php echo $base_url; ?>/login" class="toggle-link">Already have an account?
                    <strong>Login</strong></a>
            </div>
        </div>
    </div>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode">
        🌙
    </button>
    <script src="<?php echo $base_url; ?>/assets/js/theme.js"></script>

    <script>
        const roleSelect = document.getElementById('role');
        const passcodeGroup = document.getElementById('passcode-group');

        roleSelect.addEventListener('change', function () {
            if (this.value === 'admin') {
                passcodeGroup.style.display = 'block';
            } else {
                passcodeGroup.style.display = 'none';
            }
        });

        document.getElementById('register-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('<?php echo $base_url; ?>/api/auth.php?action=register', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Registration successful! Please login.');
                        window.location.href = '/login';
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