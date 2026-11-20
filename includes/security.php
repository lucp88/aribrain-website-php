<?php
/**
 * Session hardening and CSRF protection.
 */

function start_secure_session() {
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify() {
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && $sent !== '' && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/**
 * Reject any POST request without a valid CSRF token.
 */
function csrf_enforce() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || csrf_verify()) {
        return;
    }

    // PHP drops the whole POST body when it exceeds post_max_size.
    $tooLarge = empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;

    http_response_code($tooLarge ? 413 : 400);
    $message = $tooLarge
        ? 'The submitted data was too large (upload limit: ' . ini_get('post_max_size') . ').'
        : 'Your session has expired or the form was invalid.';
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Request rejected</title></head>'
        . '<body style="font-family: sans-serif; max-width: 480px; margin: 80px auto; padding: 0 24px; color: #1a1a2e;">'
        . '<h1 style="font-size: 1.25rem;">Request rejected</h1>'
        . '<p>' . htmlspecialchars($message) . ' Please go back, reload the page and try again.</p>'
        . '</body></html>';
    exit;
}
