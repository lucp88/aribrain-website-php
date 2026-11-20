<?php
// Shown by index.php instead of public pages while the site is under construction.
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = $auth ?? new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password';
    } else {
        $result = $auth->login($email, $password);
        if ($result['success']) {
            // Back to the page that was requested.
            $requested = $page ?? 'home';
            header('Location: index.php' . ($requested !== 'home' ? '?page=' . urlencode($requested) : ''));
            exit;
        }
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Under Construction</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .gate-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }
        .gate-box {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 16px;
            padding: 48px 40px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        }
        .gate-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .gate-header .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            text-decoration: none;
        }
        .gate-header .logo-icon {
            color: #6bcf8e;
        }
        .gate-header .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        .gate-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: white;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 14px;
            border-radius: 100px;
            margin-bottom: 20px;
        }
        .gate-header h1 {
            font-size: 1.375rem;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 12px;
        }
        .gate-header p {
            font-size: 0.9375rem;
            color: #6b7280;
            line-height: 1.6;
        }
        .gate-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .gate-form label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        .gate-form input {
            padding: 12px 16px;
            font-size: 1rem;
            font-family: inherit;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.2s;
            background: #f9fafb;
        }
        .gate-form input:focus {
            outline: none;
            border-color: #6bcf8e;
            box-shadow: 0 0 0 3px rgba(107, 207, 142, 0.2);
            background: white;
        }
        .gate-submit {
            margin-top: 4px;
            padding: 13px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            color: #1a1a2e;
            background: #6bcf8e;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .gate-submit:hover {
            background: #5ab87d;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107, 207, 142, 0.4);
        }
        .gate-error {
            padding: 12px 16px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 10px;
            font-size: 0.875rem;
            text-align: center;
        }
        .gate-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }
        .gate-footer p {
            font-size: 0.8125rem;
            color: #9ca3af;
        }
        @media (max-width: 480px) {
            .gate-box {
                padding: 36px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="gate-container">
        <div class="gate-box">
            <div class="gate-header">
                <a href="#" class="logo">
                    <span class="logo-icon">
                        <svg width="36" height="36" viewBox="0 0 32 32" fill="none">
                            <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 20C10 20 12 12 16 12C20 12 22 20 22 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="16" cy="10" r="2" fill="currentColor"/>
                            <circle cx="11" cy="18" r="1.5" fill="currentColor"/>
                            <circle cx="21" cy="18" r="1.5" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="logo-text"><?php echo SITE_NAME; ?></span>
                </a>
                <div><span class="gate-badge">Under Construction</span></div>
                <h1>We're building something great</h1>
                <p>Our website is currently under development. If you are a registered user, please log in below to preview the work in progress.</p>
            </div>

            <?php if ($error): ?>
                <div class="gate-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="gate-form">
                <?php echo csrf_field(); ?>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="you@example.com">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="gate-submit">Log In</button>
            </form>

            <div class="gate-footer">
                <p><a href="index.php?page=forgot-password" style="color: #6b7280;">Forgot password?</a></p>
                <p><?php echo SITE_NAME; ?> &middot; Leiden University</p>
            </div>
        </div>
    </div>
</body>
</html>
