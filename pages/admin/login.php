<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$auth = new Auth();

if ($auth->canAccessAdminPanel()) {
    header('Location: index.php?page=admin/dashboard');
    exit;
}

$error = '';
$success = '';
$showSetup = !$auth->adminExists();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'setup') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$showSetup) {
            $error = 'An admin account already exists';
        } elseif ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            $result = $auth->createFirstAdmin($name, $email, $password);
            if ($result['success']) {
                $success = 'Admin account created. You can now log in.';
                $showSetup = false;
            } else {
                $error = $result['error'];
            }
        }
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Please enter email and password';
        } else {
            $result = $auth->login($email, $password, Auth::ADMIN_PANEL_ROLES);
            if ($result['success']) {
                header('Location: index.php?page=admin/dashboard');
                exit;
            }
            $error = $result['error'];
        }
    }
}

$authTitle = $showSetup ? 'Create Admin Account' : 'Admin Login';
$authHeading = $authTitle;
require __DIR__ . '/../../includes/auth-layout-start.php';
?>
            <?php if ($error): ?>
                <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($showSetup): ?>
                <form method="POST" class="admin-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="setup">
                    <div class="admin-form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required autocomplete="name">
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
                    <?php echo csrf_field(); ?>
                    <div class="admin-form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required autocomplete="email">
                    </div>
                    <div class="admin-form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                </form>
            <?php endif; ?>
<?php
$authFooterLinks = $showSetup
    ? [['Back to website', 'index.php']]
    : [['Forgot password?', 'index.php?page=forgot-password'], ['Back to website', 'index.php']];
require __DIR__ . '/../../includes/auth-layout-end.php';
