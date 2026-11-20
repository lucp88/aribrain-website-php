<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

$auth = new Auth();
$db = Database::getInstance()->getConnection();
$error = '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = $auth->issueToken($user['id'], Auth::RESET_TTL);
            send_password_reset_email($user['name'], $user['email'], $token);
        }

        // Same response whether or not the account exists.
        $submitted = true;
    }
}

$authTitle = 'Forgot Password';
$authHeading = 'Forgot Password';
require __DIR__ . '/../includes/auth-layout-start.php';
?>
            <?php if ($error): ?>
                <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($submitted): ?>
                <div class="admin-alert admin-alert-success">
                    If an account with that email exists, a password reset link has been sent. The link expires in 1 hour.
                </div>
                <div style="text-align: center; margin-top: 16px;">
                    <a href="index.php?page=user/login" class="btn btn-primary btn-block">Back to Login</a>
                </div>
            <?php else: ?>
                <p style="color: var(--color-text-muted); font-size: 0.9375rem; margin-bottom: 24px; text-align: center;">
                    Enter your email and we'll send you a link to reset your password.
                </p>
                <form method="POST" class="admin-form">
                    <?php echo csrf_field(); ?>
                    <div class="admin-form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required autocomplete="email" autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                </form>
            <?php endif; ?>
<?php
$authFooterLinks = [['Back to login', 'index.php?page=user/login']];
require __DIR__ . '/../includes/auth-layout-end.php';
