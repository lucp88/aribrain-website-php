<?php
require_once __DIR__ . '/../../includes/admin/auth.php';
require_once __DIR__ . '/../../config/config.php';

$auth = new AdminAuth();
$error = '';
$success = false;

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$admin = $auth->findByResetToken($token);
$validToken = $admin !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password)) {
        $error = 'Password is required';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $success = $auth->resetPasswordWithToken($token, $password);
        if (!$success) {
            $error = 'Could not reset password. The link may have expired.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - <?php echo SITE_NAME; ?></title>
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
                <?php if ($success): ?>
                    <h1>Password Reset</h1>
                <?php elseif ($validToken): ?>
                    <h1>Choose a New Password</h1>
                <?php else: ?>
                    <h1>Invalid Link</h1>
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
                <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="admin-alert admin-alert-success">
                    Your admin password has been reset. You can now log in with your new password.
                </div>
                <div style="text-align: center; margin-top: 16px;">
                    <a href="index.php?page=admin/login" class="btn btn-primary btn-block">Go to Login</a>
                </div>
            <?php elseif ($validToken): ?>
                <p style="color: var(--color-text-muted); font-size: 0.9375rem; margin-bottom: 24px; text-align: center;">
                    Hi <strong><?php echo htmlspecialchars($admin['username']); ?></strong>, choose a new password for your admin account.
                </p>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="admin-form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                    </div>
                    <div class="admin-form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>
            <?php else: ?>
                <p style="color: var(--color-text-muted); font-size: 0.9375rem; text-align: center;">
                    This reset link is invalid or has expired. Request a new one below.
                </p>
                <div style="text-align: center; margin-top: 16px;">
                    <a href="index.php?page=admin/forgot-password" class="btn btn-primary btn-block">Request a new reset link</a>
                </div>
            <?php endif; ?>

            <div class="admin-login-footer">
                <a href="index.php?page=admin/login">Back to login</a>
            </div>
        </div>
    </div>
</body>
</html>
