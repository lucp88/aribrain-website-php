<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Send an HTML email using the ARIbrain template.
 *
 * $options:
 *   subtitle      Line under the logo in the header (plain text)
 *   heading       Main heading (plain text)
 *   body_html     Intro paragraph (HTML; escape user data before passing it)
 *   button_label  Call-to-action text (plain text)
 *   button_url    Call-to-action link
 *   footnote_html Small print under the button (HTML)
 */
function send_branded_email($to, $subject, array $options) {
    $e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    $siteName = $e(SITE_NAME);
    $url = $e($options['button_url']);

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
    <h1 style="margin: 0; color: #6bcf8e; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">' . $siteName . '</h1>
    <p style="margin: 8px 0 0; color: rgba(255,255,255,0.7); font-size: 14px;">' . $e($options['subtitle'] ?? SITE_TAGLINE) . '</p>
</td>
</tr>

<tr>
<td style="padding: 48px;">
    <h2 style="margin: 0 0 8px; color: #1a1a2e; font-size: 22px; font-weight: 600;">' . $e($options['heading']) . '</h2>
    <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">' . $options['body_html'] . '</p>

    <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding: 8px 0 32px;">
        <a href="' . $url . '" style="display: inline-block; background: #6bcf8e; color: #1a1a2e; text-decoration: none; font-weight: 600; font-size: 16px; padding: 14px 40px; border-radius: 8px;">' . $e($options['button_label']) . '</a>
    </td></tr>
    </table>

    <p style="margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.6;">' . ($options['footnote_html'] ?? '') . '</p>

    <div style="background: #f9fafb; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <p style="margin: 0 0 8px; color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Or copy this link:</p>
        <p style="margin: 0; color: #6b7280; font-size: 13px; word-break: break-all;">' . $url . '</p>
    </div>
</td>
</tr>

<tr>
<td style="background: #f9fafb; padding: 24px 48px; border-top: 1px solid #e5e7eb; text-align: center;">
    <p style="margin: 0; color: #9ca3af; font-size: 13px;">&copy; ' . date('Y') . ' ' . $siteName . ' &middot; Leiden University</p>
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

    return @mail($to, $subject, $html, $headers);
}

function send_invitation_email($name, $email, $role, $token) {
    $e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    return send_branded_email($email, "You're invited to join " . SITE_NAME, [
        'heading' => 'Welcome aboard, ' . $name . '!',
        'body_html' => 'You\'ve been invited to join <strong>' . $e(SITE_NAME) . '</strong> as <strong style="color: #6bcf8e;">'
            . $e(ucfirst($role)) . '</strong>. Set up your password to get started.',
        'button_label' => 'Set Your Password',
        'button_url' => SITE_URL . '/index.php?page=set-password&token=' . $token,
        'footnote_html' => 'This invitation link will expire in <strong>7 days</strong>. If you didn\'t expect this email, you can safely ignore it.',
    ]);
}

function send_password_reset_email($name, $email, $token) {
    $e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    return send_branded_email($email, 'Reset your ' . SITE_NAME . ' password', [
        'heading' => 'Reset your password',
        'body_html' => 'Hi ' . $e($name) . ', we received a request to reset your password. Click the button below to choose a new one.',
        'button_label' => 'Reset Password',
        'button_url' => SITE_URL . '/index.php?page=set-password&token=' . $token,
        'footnote_html' => 'This link will expire in <strong>1 hour</strong>. If you didn\'t request a password reset, you can safely ignore this email &mdash; your password will remain unchanged.',
    ]);
}
