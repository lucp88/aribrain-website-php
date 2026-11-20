<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/mail.php';

$auth = new Auth();
$auth->requireAdmin();

$currentUser = $auth->currentUser();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['id'] ?? 0);

    if (in_array($action, ['toggle_status', 'delete'], true) && $userId === (int)$currentUser['id']) {
        $error = 'You cannot deactivate or delete your own account';
    } elseif ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';

        if (empty($name) || empty($email)) {
            $error = 'Name and email are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } elseif (!in_array($role, ['admin', 'editor', 'viewer'], true)) {
            $error = 'Invalid role';
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'A user with this email already exists';
            } else {
                $stmt = $db->prepare("INSERT INTO users (name, email, role, status, invited_by) VALUES (?, ?, ?, 'invited', ?)");
                $stmt->execute([$name, $email, $role, $currentUser['id']]);
                $token = $auth->issueToken($db->lastInsertId(), Auth::INVITATION_TTL);

                if (send_invitation_email($name, $email, $role, $token)) {
                    $message = "Invitation sent to {$email}";
                } else {
                    $message = "User created but email could not be sent. You can resend the invitation.";
                }
            }
        }
    } elseif ($action === 'resend') {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'invited'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $token = $auth->issueToken($user['id'], Auth::INVITATION_TTL);
            $sent = send_invitation_email($user['name'], $user['email'], $user['role'], $token);
            $message = $sent ? "Invitation resent to {$user['email']}" : "Could not send email. Please try again.";
        } else {
            $error = 'User not found or already active';
        }
    } elseif ($action === 'toggle_status') {
        $stmt = $db->prepare("SELECT status FROM users WHERE id = ? AND status IN ('active', 'inactive')");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $userId]);
            $message = "User status updated to {$newStatus}";
        }
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'User deleted';
    }
}

$users = $db->query("SELECT u.*, inviter.name AS invited_by_name FROM users u LEFT JOIN users inviter ON u.invited_by = inviter.id ORDER BY u.created_at DESC")->fetchAll();

$adminTitle = 'Users';
$activePage = 'users';
require __DIR__ . '/../../includes/admin/layout-start.php';
?>
                <?php if ($message): ?>
                    <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="admin-section">
                    <h2>Invite New User</h2>
                    <form method="POST" class="admin-form-grid"><?php echo csrf_field(); ?>
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
                                            <form method="POST" style="display:inline"><?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="resend">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline">Resend</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ((int)$user['id'] === (int)$currentUser['id']): ?>
                                            <span class="admin-badge">You</span>
                                        <?php else: ?>
                                            <?php if ($user['status'] !== 'invited'): ?>
                                                <form method="POST" style="display:inline"><?php echo csrf_field(); ?>
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline">
                                                        <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this user?')"><?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php require __DIR__ . '/../../includes/admin/layout-end.php'; ?>
