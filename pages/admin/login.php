<?php
require_once __DIR__ . '/../../includes/admin/auth.php';

$auth = new AdminAuth();

if ($auth->isLoggedIn()) {
    header('Location: index.php?page=admin/dashboard');
    exit;
}

$error = '';
$success = '';
$showSetup = false;

try {
    $showSetup = !$auth->adminExists();
} catch (Exception $e) {
    $showSetup = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'setup') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            $result = $auth->createAdmin($username, $email, $password);
            if ($result['success']) {
                $success = 'Admin account created. You can now log in.';
                $showSetup = false;
            } else {
                $error = $result['error'];
            }
        }
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password';
        } else {
            $result = $auth->login($username, $password);
            if ($result['success']) {
                header('Location: index.php?page=admin/dashboard');
                exit;
            } else {
                $error = $result['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-login-container">
        <div class="admin-login-box">
            <div class="admin-login-header">
                <a href="index.php" class="logo">
                    <img src="assets/images/aribrain-logo.svg" alt="ARIbrain" width="40" height="40">
                    <span><?php echo SITE_NAME; ?></span>
                </a>
                <h1><?php echo $showSetup ? 'Create Admin Account' : 'Admin Login'; ?></h1>
            </div>
            
            <?php if ($error): ?>
                <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($showSetup): ?>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="setup">
                    <div class="admin-form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required autocomplete="username">
                    </div>
                    <div class="admin-form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required autocomplete="email">
                    </div>
                    <div class="admin-form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="admin-form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>
            <?php else: ?>
                <form method="POST" class="admin-form">
                    <div class="admin-form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" required autocomplete="username">
                    </div>
                    <div class="admin-form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                </form>
            <?php endif; ?>
            
            <div class="admin-login-footer">
                <a href="index.php">Back to website</a>
            </div>
        </div>
    </div>
</body>
</html>
