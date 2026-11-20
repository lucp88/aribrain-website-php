<?php
require_once __DIR__ . '/../../includes/admin/auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = new AdminAuth();
$auth->requireLogin();
$auth->requireAdmin();

$currentAdmin = $auth->getCurrentAdmin();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';

        if (empty($name) || empty($email)) {
            $error = 'Name and email are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } elseif (!in_array($role, ['admin', 'editor', 'viewer'])) {
            $error = 'Invalid role';
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'A user with this email already exists';
            } else {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

                $stmt = $db->prepare("INSERT INTO users (name, email, role, invitation_token, token_expires_at, status, invited_by) VALUES (?, ?, ?, ?, ?, 'invited', ?)");
                $stmt->execute([$name, $email, $role, $token, $expiresAt, $currentAdmin['id']]);

                $sent = sendInvitationEmail($name, $email, $role, $token);
                if ($sent) {
                    $message = "Invitation sent to {$email}";
                } else {
                    $message = "User created but email could not be sent. You can resend the invitation.";
                }
            }
        }
    } elseif ($action === 'resend') {
        $userId = intval($_POST['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'invited'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
            $stmt = $db->prepare("UPDATE users SET invitation_token = ?, token_expires_at = ? WHERE id = ?");
            $stmt->execute([$token, $expiresAt, $userId]);

            $sent = sendInvitationEmail($user['name'], $user['email'], $user['role'], $token);
            $message = $sent ? "Invitation resent to {$user['email']}" : "Could not send email. Please try again.";
        } else {
            $error = 'User not found or already active';
        }
    } elseif ($action === 'toggle_status') {
        $userId = intval($_POST['id'] ?? 0);
        $stmt = $db->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $userId]);
            $message = "User status updated to {$newStatus}";
        }
    } elseif ($action === 'delete') {
        $userId = intval($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'User deleted';
    }
}

$users = $db->query("SELECT users.*, admins.username AS invited_by_name FROM users LEFT JOIN admins ON users.invited_by = admins.id ORDER BY users.created_at DESC")->fetchAll();

function sendInvitationEmail($name, $email, $role, $token) {
    $setupUrl = SITE_URL . '/index.php?page=set-password&token=' . $token;
    $roleName = ucfirst($role);

    $subject = "You're invited to join " . SITE_NAME;

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

<!-- Header -->
<tr>
<td style="background: linear-gradient(135deg, #2d3436 0%, #1a1a2e 100%); padding: 40px 48px; text-align: center;">
    <h1 style="margin: 0; color: #6bcf8e; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">' . SITE_NAME . '</h1>
    <p style="margin: 8px 0 0; color: rgba(255,255,255,0.7); font-size: 14px;">All-Resolutions Inference for fMRI</p>
</td>
</tr>

<!-- Body -->
<tr>
<td style="padding: 48px;">
    <h2 style="margin: 0 0 8px; color: #1a1a2e; font-size: 22px; font-weight: 600;">Welcome aboard, ' . htmlspecialchars($name) . '!</h2>
    <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">
        You\'ve been invited to join <strong>' . SITE_NAME . '</strong> as <strong style="color: #6bcf8e;">' . $roleName . '</strong>. Set up your password to get started.
    </p>

    <!-- Button -->
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding: 8px 0 32px;">
        <a href="' . htmlspecialchars($setupUrl) . '" style="display: inline-block; background: #6bcf8e; color: #1a1a2e; text-decoration: none; font-weight: 600; font-size: 16px; padding: 14px 40px; border-radius: 8px;">
            Set Your Password
        </a>
    </td></tr>
    </table>

    <p style="margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.6;">
        This invitation link will expire in <strong>7 days</strong>. If you didn\'t expect this email, you can safely ignore it.
    </p>

    <!-- Link fallback -->
    <div style="background: #f9fafb; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <p style="margin: 0 0 8px; color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Or copy this link:</p>
        <p style="margin: 0; color: #6b7280; font-size: 13px; word-break: break-all;">' . htmlspecialchars($setupUrl) . '</p>
    </div>
</td>
</tr>

<!-- Footer -->
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php $activePage = 'users'; require __DIR__ . '/../../includes/admin/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Users</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($currentAdmin['username']); ?></span>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="admin-section">
                    <h2>Invite New User</h2>
                    <form method="POST" class="admin-form-grid">
                        <input type="hidden" name="action" value="create">

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Name *</label>
                                <input type="text" id="name" name="name" required placeholder="John Doe">
                            </div>
                            <div class="admin-form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required placeholder="john@example.com">
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="role">Role *</label>
                                <select id="role" name="role" required>
                                    <option value="viewer">Viewer</option>
                                    <option value="editor">Editor</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="admin-form-group" style="display: flex; align-items: flex-end;">
                                <button type="submit" class="btn btn-primary">Send Invitation</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="admin-section">
                    <h2>All Users (<?php echo count($users); ?>)</h2>
                    <?php if (empty($users)): ?>
                        <p class="admin-empty">No users yet. Invite someone above.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Invited By</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr<?php echo $user['status'] === 'inactive' ? ' class="admin-row-hidden"' : ''; ?>>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="admin-badge admin-badge-<?php echo $user['role'] === 'admin' ? 'featured' : ($user['role'] === 'editor' ? 'success' : 'warning'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'invited'): ?>
                                            <span class="admin-badge" style="background: #3b82f6; color: white;">Invited</span>
                                        <?php elseif ($user['status'] === 'active'): ?>
                                            <span class="admin-badge admin-badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="admin-badge">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['invited_by_name'] ?? '-'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="admin-table-actions">
                                        <?php if ($user['status'] === 'invited'): ?>
                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="action" value="resend">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline">Resend</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($user['status'] !== 'invited'): ?>
                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline">
                                                    <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this user?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
