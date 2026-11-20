<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();
$error = '';
$submitted = false;

function sendPasswordResetEmail($name, $email, $token) {
    $resetUrl = SITE_URL . '/index.php?page=set-password&token=' . $token;

    $subject = "Reset your " . SITE_NAME . " password";

    $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7f6; font-family: \'Helvetica Neue\', Arial, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7f6; padding: 40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08);">

<tr>
<td style="background: linear-gradient(135deg, #2d3436 0%, #1a1a2e 100%); padding: 40px 48px; text-align: center;">
    <h1 style="margin: 0; color: #6bcf8e; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">' . SITE_NAME . '</h1>
    <p style="margin: 8px 0 0; color: rgba(255,255,255,0.7); font-size: 14px;">All-Resolutions Inference for fMRI</p>
</td>
</tr>

<tr>
<td style="padding: 48px;">
    <h2 style="margin: 0 0 8px; color: #1a1a2e; font-size: 22px; font-weight: 600;">Reset your password</h2>
    <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">
        Hi ' . htmlspecialchars($name) . ', we received a request to reset your password. Click the button below to choose a new one.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding: 8px 0 32px;">
        <a href="' . htmlspecialchars($resetUrl) . '" style="display: inline-block; background: #6bcf8e; color: #1a1a2e; text-decoration: none; font-weight: 600; font-size: 16px; padding: 14px 40px; border-radius: 8px;">
            Reset Password
        </a>
    </td></tr>
    </table>

    <p style="margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.6;">
        This link will expire in <strong>1 hour</strong>. If you didn\'t request a password reset, you can safely ignore this email &mdash; your password will remain unchanged.
    </p>

    <div style="background: #f9fafb; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <p style="margin: 0 0 8px; color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Or copy this link:</p>
        <p style="margin: 0; color: #6b7280; font-size: 13px; word-break: break-all;">' . htmlspecialchars($resetUrl) . '</p>
    </div>
</td>
</tr>

<tr>
<td style="background: #f9fafb; padding: 24px 48px; border-top: 1px solid #e5e7eb; text-align: center;">
    <p style="margin: 0; color: #9ca3af; font-size: 13px;">&copy; ' . date('Y') . ' ' . SITE_NAME . ' &middot; Leiden University</p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>';

    $headers = "From: " . SITE_NAME . " <noreply@aribrain.eu>\r\n";
    $headers .= "Reply-To: " . CONTACT_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $html, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $db->prepare("SELECT id, name, email, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'active') {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $db->prepare("UPDATE users SET invitation_token = ?, token_expires_at = ? WHERE id = ?");
            $stmt->execute([$token, $expiresAt, $user['id']]);

            sendPasswordResetEmail($user['name'], $user['email'], $token);
        }

        $submitted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
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
                <h1>Forgot Password</h1>
            </div>

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
                    <div class="admin-form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required autocomplete="email" autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                </form>
            <?php endif; ?>

            <div class="admin-login-footer">
                <a href="index.php?page=user/login">Back to login</a>
            </div>
        </div>
    </div>
</body>
</html>
