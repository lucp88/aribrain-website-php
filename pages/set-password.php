<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();
$error = '';
$success = false;
$validToken = false;
$user = null;

$token = $_GET['token'] ?? '';

if (!empty($token)) {
    $stmt = $db->prepare("SELECT * FROM users WHERE invitation_token = ? AND token_expires_at > NOW() AND status = 'invited'");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    $validToken = $user !== false;
}

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
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, invitation_token = NULL, token_expires_at = NULL, status = 'active' WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password - <?php echo SITE_NAME; ?></title>
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
                    <h1>You're all set!</h1>
                <?php elseif ($validToken): ?>
                    <h1>Set Your Password</h1>
                <?php else: ?>
                    <h1>Invalid Link</h1>
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
                <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="admin-alert admin-alert-success">
                    Your password has been set. You can now log in.
                </div>
                <div style="text-align: center; margin-top: 16px;">
                    <a href="index.php?page=user/login" class="btn btn-primary btn-block">Go to Login</a>
                </div>
            <?php elseif ($validToken): ?>
                <p style="color: var(--color-text-muted); font-size: 0.9375rem; margin-bottom: 24px; text-align: center;">
                    Welcome, <strong><?php echo htmlspecialchars($user['name']); ?></strong>! Choose a password to activate your account.
                </p>
                <form method="POST" class="admin-form">
                    <div class="admin-form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                    </div>
                    <div class="admin-form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Set Password</button>
                </form>
            <?php else: ?>
                <p style="color: var(--color-text-muted); font-size: 0.9375rem; text-align: center;">
                    This invitation link is invalid or has expired. Please contact your administrator for a new invitation.
                </p>
            <?php endif; ?>

            <div class="admin-login-footer">
                <a href="index.php">Back to website</a>
            </div>
        </div>
    </div>
</body>
</html>
