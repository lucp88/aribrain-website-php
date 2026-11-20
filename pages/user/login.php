<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter email and password';
    } else {
        $result = $auth->login($email, $password);
        if ($result['success']) {
            header('Location: index.php');
            exit;
        }
        $error = $result['error'];
    }
}

$authTitle = 'Login';
$authHeading = 'Login';
require __DIR__ . '/../../includes/auth-layout-start.php';
?>
            <?php if ($error): ?>
                <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

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
<?php
$authFooterLinks = [['Forgot password?', 'index.php?page=forgot-password'], ['Back to website', 'index.php']];
require __DIR__ . '/../../includes/auth-layout-end.php';
