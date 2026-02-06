<?php
require_once __DIR__ . '/../../includes/admin/auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = new AdminAuth();
$auth->requireLogin();

$currentAdmin = $auth->getCurrentAdmin();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reply') {
        $questionId = intval($_POST['question_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        
        if (empty($content) || !$questionId) {
            $error = 'Reply content is required';
        } else {
            $stmt = $db->prepare("INSERT INTO forum_replies (question_id, content, author_name, is_admin_reply, admin_id) VALUES (?, ?, ?, 1, ?)");
            $stmt->execute([$questionId, $content, $currentAdmin['username'], $currentAdmin['id']]);
            
            $db->prepare("UPDATE forum_questions SET is_answered = 1 WHERE id = ?")->execute([$questionId]);
            $message = 'Reply posted successfully';
        }
    } elseif ($action === 'delete_question') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM forum_questions WHERE id = ?")->execute([$id]);
            $message = 'Question deleted';
        }
    } elseif ($action === 'delete_reply') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM forum_replies WHERE id = ?")->execute([$id]);
            $message = 'Reply deleted';
        }
    } elseif ($action === 'toggle_approval') {
        $id = intval($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'question';
        if ($id) {
            if ($type === 'question') {
                $db->prepare("UPDATE forum_questions SET is_approved = NOT is_approved WHERE id = ?")->execute([$id]);
            } else {
                $db->prepare("UPDATE forum_replies SET is_approved = NOT is_approved WHERE id = ?")->execute([$id]);
            }
            $message = 'Status updated';
        }
    } elseif ($action === 'toggle_featured') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("UPDATE forum_questions SET is_featured = NOT is_featured WHERE id = ?")->execute([$id]);
            $message = 'Featured status updated';
        }
    } elseif ($action === 'create_tag') {
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? '#6bcf8e';
        if ($name) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
            $stmt = $db->prepare("INSERT INTO forum_tags (name, slug, color) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $color]);
            $message = 'Tag created';
        }
    } elseif ($action === 'delete_tag') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM forum_tags WHERE id = ?")->execute([$id]);
            $message = 'Tag deleted';
        }
    }
}

$selectedQuestion = null;
$replies = [];
if (isset($_GET['q'])) {
    $qId = intval($_GET['q']);
    $stmt = $db->prepare("SELECT q.*, GROUP_CONCAT(t.name) as tag_names FROM forum_questions q LEFT JOIN forum_question_tags qt ON q.id = qt.question_id LEFT JOIN forum_tags t ON qt.tag_id = t.id WHERE q.id = ? GROUP BY q.id");
    $stmt->execute([$qId]);
    $selectedQuestion = $stmt->fetch();
    
    if ($selectedQuestion) {
        $stmt = $db->prepare("SELECT r.*, a.username as admin_username FROM forum_replies r LEFT JOIN admins a ON r.admin_id = a.id WHERE r.question_id = ? ORDER BY r.created_at ASC");
        $stmt->execute([$qId]);
        $replies = $stmt->fetchAll();
    }
}

$questions = $db->query("SELECT q.*, 
    (SELECT COUNT(*) FROM forum_replies WHERE question_id = q.id) as reply_count,
    GROUP_CONCAT(t.name) as tag_names
    FROM forum_questions q 
    LEFT JOIN forum_question_tags qt ON q.id = qt.question_id 
    LEFT JOIN forum_tags t ON qt.tag_id = t.id 
    GROUP BY q.id 
    ORDER BY q.created_at DESC")->fetchAll();

$tags = $db->query("SELECT t.*, (SELECT COUNT(*) FROM forum_question_tags WHERE tag_id = t.id) as usage_count FROM forum_tags t ORDER BY t.name")->fetchAll();

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return date('M j', $time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Admin - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="index.php" class="logo">
                    <img src="assets/images/aribrain-logo.svg" alt="ARIbrain" width="32" height="32">
                    <span><?php echo SITE_NAME; ?></span>
                </a>
            </div>
            
            <nav class="admin-nav">
                <a href="index.php?page=admin/dashboard" class="admin-nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
                <a href="index.php?page=admin/publications" class="admin-nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Publications
                </a>
                <a href="index.php?page=admin/forum" class="admin-nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Forum
                </a>
            </nav>
            
            <div class="admin-sidebar-footer">
                <a href="index.php?page=admin/dashboard&action=logout" class="admin-nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </div>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Forum Management</h1>
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
                
                <?php if ($selectedQuestion): ?>
                <div class="admin-section">
                    <div class="admin-section-header">
                        <a href="index.php?page=admin/forum" class="btn btn-outline btn-sm">&larr; Back to Questions</a>
                    </div>
                    
                    <div class="admin-question-detail">
                        <div class="admin-question-header">
                            <h2><?php echo htmlspecialchars($selectedQuestion['title']); ?></h2>
                            <div class="admin-question-meta">
                                <span>By <?php echo htmlspecialchars($selectedQuestion['author_name']); ?></span>
                                <span><?php echo date('M j, Y g:i A', strtotime($selectedQuestion['created_at'])); ?></span>
                                <span><?php echo $selectedQuestion['views']; ?> views</span>
                                <?php if ($selectedQuestion['tag_names']): ?>
                                    <span>Tags: <?php echo htmlspecialchars($selectedQuestion['tag_names']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="admin-question-content">
                            <?php echo nl2br(htmlspecialchars($selectedQuestion['content'])); ?>
                        </div>
                        
                        <div class="admin-question-actions">
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="toggle_featured">
                                <input type="hidden" name="id" value="<?php echo $selectedQuestion['id']; ?>">
                                <button type="submit" class="btn btn-sm <?php echo $selectedQuestion['is_featured'] ? 'btn-primary' : 'btn-outline'; ?>">
                                    <?php echo $selectedQuestion['is_featured'] ? 'Unfeature' : 'Feature'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="toggle_approval">
                                <input type="hidden" name="type" value="question">
                                <input type="hidden" name="id" value="<?php echo $selectedQuestion['id']; ?>">
                                <button type="submit" class="btn btn-sm <?php echo $selectedQuestion['is_approved'] ? 'btn-outline' : 'btn-primary'; ?>">
                                    <?php echo $selectedQuestion['is_approved'] ? 'Hide' : 'Approve'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this question and all replies?')">
                                <input type="hidden" name="action" value="delete_question">
                                <input type="hidden" name="id" value="<?php echo $selectedQuestion['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="admin-replies-section">
                        <h3><?php echo count($replies); ?> Replies</h3>
                        
                        <?php foreach ($replies as $reply): ?>
                        <div class="admin-reply <?php echo $reply['is_admin_reply'] ? 'admin-reply-team' : ''; ?> <?php echo !$reply['is_approved'] ? 'admin-reply-hidden' : ''; ?>">
                            <div class="admin-reply-content">
                                <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                            </div>
                            <div class="admin-reply-footer">
                                <span>
                                    <?php echo htmlspecialchars($reply['is_admin_reply'] ? $reply['admin_username'] : $reply['author_name']); ?>
                                    <?php if ($reply['is_admin_reply']): ?><span class="admin-team-badge">Team</span><?php endif; ?>
                                </span>
                                <span><?php echo timeAgo($reply['created_at']); ?></span>
                                <?php if (!$reply['is_approved']): ?><span class="admin-hidden-badge">Hidden</span><?php endif; ?>
                                <div class="admin-reply-actions">
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="action" value="toggle_approval">
                                        <input type="hidden" name="type" value="reply">
                                        <input type="hidden" name="id" value="<?php echo $reply['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline"><?php echo $reply['is_approved'] ? 'Hide' : 'Show'; ?></button>
                                    </form>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this reply?')">
                                        <input type="hidden" name="action" value="delete_reply">
                                        <input type="hidden" name="id" value="<?php echo $reply['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="admin-reply-form">
                            <h4>Reply as Team</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="question_id" value="<?php echo $selectedQuestion['id']; ?>">
                                <div class="admin-form-group">
                                    <textarea name="content" rows="4" placeholder="Write your reply..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Reply</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                
                <div class="admin-section">
                    <h2>Questions (<?php echo count($questions); ?>)</h2>
                    
                    <?php if (empty($questions)): ?>
                        <p class="admin-empty">No questions yet.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Author</th>
                                    <th>Tags</th>
                                    <th>Replies</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $q): ?>
                                <tr class="<?php echo !$q['is_approved'] ? 'admin-row-hidden' : ''; ?>">
                                    <td>
                                        <a href="index.php?page=admin/forum&q=<?php echo $q['id']; ?>">
                                            <?php echo htmlspecialchars(substr($q['title'], 0, 50)) . (strlen($q['title']) > 50 ? '...' : ''); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($q['author_name']); ?></td>
                                    <td><?php echo htmlspecialchars($q['tag_names'] ?? '-'); ?></td>
                                    <td><?php echo $q['reply_count']; ?></td>
                                    <td>
                                        <?php if ($q['is_featured']): ?>
                                            <span class="admin-badge admin-badge-featured">Featured</span>
                                        <?php endif; ?>
                                        <?php if ($q['is_answered']): ?>
                                            <span class="admin-badge admin-badge-success">Answered</span>
                                        <?php elseif (!$q['is_approved']): ?>
                                            <span class="admin-badge admin-badge-warning">Hidden</span>
                                        <?php else: ?>
                                            <span class="admin-badge">Open</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo timeAgo($q['created_at']); ?></td>
                                    <td class="admin-table-actions">
                                        <a href="index.php?page=admin/forum&q=<?php echo $q['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this question?')">
                                            <input type="hidden" name="action" value="delete_question">
                                            <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="admin-section">
                    <h2>Tags</h2>
                    
                    <form method="POST" class="admin-inline-form">
                        <input type="hidden" name="action" value="create_tag">
                        <input type="text" name="name" placeholder="New tag name" required>
                        <input type="color" name="color" value="#6bcf8e">
                        <button type="submit" class="btn btn-primary btn-sm">Add Tag</button>
                    </form>
                    
                    <table class="admin-table admin-table-compact">
                        <thead>
                            <tr>
                                <th>Tag</th>
                                <th>Color</th>
                                <th>Used</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tags as $tag): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tag['name']); ?></td>
                                <td><span style="display:inline-block;width:20px;height:20px;background:<?php echo htmlspecialchars($tag['color']); ?>;border-radius:4px;"></span></td>
                                <td><?php echo $tag['usage_count']; ?> questions</td>
                                <td>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this tag?')">
                                        <input type="hidden" name="action" value="delete_tag">
                                        <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
