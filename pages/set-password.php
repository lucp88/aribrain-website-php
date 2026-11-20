<?php
// Handles both invitation links (status 'invited') and password reset links (status 'active').
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth();
$error = '';
$success = false;

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$user = $auth->findUserByToken($token);
$isInvitation = $user && $user['status'] === 'invited';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password === '') {
        $error = 'Password is required';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif ($auth->setPasswordWithToken($token, $password)) {
        $success = true;
    } else {
        $error = 'Could not set password. The link may have expired.';
    }
}

if ($success) {
    $authHeading = $isInvitation ? "You're all set!" : 'Password updated';
} elseif ($user) {
    $authHeading = $isInvitation ? 'Set Your Password' : 'Choose a New Password';
} else {
    $authHeading = 'Invalid Link';
}
$authTitle = $authHeading;
require __DIR__ . '/../includes/auth-layout-start.php';
?>
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
            <?php elseif ($user): ?>
                <p style="color: var(--color-text-muted); font-size: 0.9375rem; margin-bottom: 24px; text-align: center;">
                    <?php if ($isInvitation): ?>
                        Welcome, <strong><?php echo htmlspecialchars($user['name']); ?></strong>! Choose a password to activate your account.
                    <?php else: ?>
                        Hi <strong><?php echo htmlspecialchars($user['name']); ?></strong>, choose a new password for your account.
                    <?php endif; ?>
                </p>
                <form method="POST" class="admin-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
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
                    This link is invalid or has expired. You can request a new password reset link, or ask an administrator to resend your invitation.
                </p>
                <div style="text-align: center; margin-top: 16px;">
                    <a href="index.php?page=forgot-password" class="btn btn-primary btn-block">Request a new reset link</a>
                </div>
            <?php endif; ?>
<?php require __DIR__ . '/../includes/auth-layout-end.php';
