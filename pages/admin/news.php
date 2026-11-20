<?php
require_once __DIR__ . '/../../includes/admin/auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = new AdminAuth();
$auth->requireLogin();

$currentAdmin = $auth->getCurrentAdmin();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $author_name = trim($_POST['author_name'] ?? '');
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $published_at = $_POST['published_at'] ?? null;

        if (empty($title) || empty($content)) {
            $error = 'Title and content are required';
        } else {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
            $slug = trim($slug, '-');

            if ($action === 'create') {
                // Ensure unique slug
                $baseSlug = $slug;
                $counter = 1;
                while (true) {
                    $stmt = $db->prepare("SELECT id FROM news_posts WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if (!$stmt->fetch()) break;
                    $slug = $baseSlug . '-' . $counter++;
                }

                $stmt = $db->prepare("INSERT INTO news_posts (title, slug, content, excerpt, author_name, is_published, is_featured, published_at, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $content, $excerpt, $author_name, $is_published, $is_featured, $published_at ?: null, $currentAdmin['id']]);
                $message = 'News post created';
            } else {
                $stmt = $db->prepare("UPDATE news_posts SET title=?, content=?, excerpt=?, author_name=?, is_published=?, is_featured=?, published_at=? WHERE id=?");
                $stmt->execute([$title, $content, $excerpt, $author_name, $is_published, $is_featured, $published_at ?: null, $id]);
                $message = 'News post updated';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $db->prepare("DELETE FROM news_posts WHERE id = ?")->execute([$id]);
            $message = 'News post deleted';
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM news_posts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$posts = $db->query("SELECT * FROM news_posts ORDER BY published_at DESC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Admin - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php $activePage = 'news'; require __DIR__ . '/../../includes/admin/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>News</h1>
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
                    <h2><?php echo $editItem ? 'Edit' : 'Add New'; ?> Post</h2>
                    <form method="POST" class="admin-form-grid">
                        <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                        <?php endif; ?>

                        <div class="admin-form-group full-width">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editItem['title'] ?? ''); ?>">
                        </div>

                        <div class="admin-form-group full-width">
                            <label for="excerpt">Excerpt (short summary for listing page)</label>
                            <textarea id="excerpt" name="excerpt" rows="2"><?php echo htmlspecialchars($editItem['excerpt'] ?? ''); ?></textarea>
                        </div>

                        <div class="admin-form-group full-width">
                            <label for="content">Content * (HTML supported)</label>
                            <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($editItem['content'] ?? ''); ?></textarea>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="author_name">Author</label>
                                <input type="text" id="author_name" name="author_name" value="<?php echo htmlspecialchars($editItem['author_name'] ?? $currentAdmin['username']); ?>">
                            </div>
                            <div class="admin-form-group">
                                <label for="published_at">Publish Date</label>
                                <input type="date" id="published_at" name="published_at" value="<?php echo $editItem['published_at'] ? date('Y-m-d', strtotime($editItem['published_at'])) : date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="is_published">
                                    <input type="checkbox" id="is_published" name="is_published" <?php echo ($editItem['is_published'] ?? 0) ? 'checked' : ''; ?>>
                                    Published
                                </label>
                            </div>
                            <div class="admin-form-group">
                                <label for="is_featured">
                                    <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($editItem['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    Featured
                                </label>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update' : 'Create'; ?></button>
                            <?php if ($editItem): ?>
                                <a href="index.php?page=admin/news" class="btn btn-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="admin-section">
                    <h2>All Posts (<?php echo count($posts); ?>)</h2>
                    <?php if (empty($posts)): ?>
                        <p class="admin-empty">No news posts yet.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($post['author_name'] ?? '-'); ?></td>
                                    <td><?php echo $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '-'; ?></td>
                                    <td>
                                        <?php if ($post['is_published']): ?>
                                            <span class="admin-badge admin-badge-success">Published</span>
                                        <?php else: ?>
                                            <span class="admin-badge">Draft</span>
                                        <?php endif; ?>
                                        <?php if ($post['is_featured']): ?>
                                            <span class="admin-badge admin-badge-featured">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="admin-table-actions">
                                        <a href="index.php?page=admin/news&edit=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this post?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
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
