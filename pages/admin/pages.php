<?php
require_once __DIR__ . '/../../includes/admin/auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = new AdminAuth();
$auth->requireLogin();

$currentAdmin = $auth->getCurrentAdmin();
$db = Database::getInstance()->getConnection();

$tab = $_GET['tab'] ?? 'product';
if (!in_array($tab, ['product', 'use-cases', 'team'])) $tab = 'product';

$message = '';
$error = '';
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $postTab = $_POST['tab'] ?? $tab;

    // --- PAGE SECTIONS (ARIbrain product page) ---
    if ($postTab === 'product') {
        if ($action === 'create' || $action === 'update') {
            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($title)) {
                $error = 'Title is required';
            } else {
                if ($action === 'create') {
                    $stmt = $db->prepare("INSERT INTO page_sections (page_slug, title, content, sort_order, is_active, created_by) VALUES ('product', ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $content, $sort_order, $is_active, $currentAdmin['id']]);
                    $message = 'Section created';
                } else {
                    $stmt = $db->prepare("UPDATE page_sections SET title=?, content=?, sort_order=?, is_active=? WHERE id=?");
                    $stmt->execute([$title, $content, $sort_order, $is_active, $id]);
                    $message = 'Section updated';
                }
            }
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM page_sections WHERE id = ?")->execute([$_POST['id']]);
            $message = 'Section deleted';
        }
    }

    // --- USE CASES ---
    if ($postTab === 'use-cases') {
        if ($action === 'create' || $action === 'update') {
            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $summary = trim($_POST['summary'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($title)) {
                $error = 'Title is required';
            } else {
                if ($action === 'create') {
                    $stmt = $db->prepare("INSERT INTO use_cases (title, summary, content, image_url, sort_order, is_featured, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $summary, $content, $image_url, $sort_order, $is_featured, $is_active, $currentAdmin['id']]);
                    $message = 'Use case created';
                } else {
                    $stmt = $db->prepare("UPDATE use_cases SET title=?, summary=?, content=?, image_url=?, sort_order=?, is_featured=?, is_active=? WHERE id=?");
                    $stmt->execute([$title, $summary, $content, $image_url, $sort_order, $is_featured, $is_active, $id]);
                    $message = 'Use case updated';
                }
            }
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM use_cases WHERE id = ?")->execute([$_POST['id']]);
            $message = 'Use case deleted';
        }
    }

    // --- TEAM MEMBERS ---
    if ($postTab === 'team') {
        if ($action === 'create' || $action === 'update') {
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $website_url = trim($_POST['website_url'] ?? '');
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $image_pos_x = intval($_POST['image_pos_x'] ?? 50);
            $image_pos_y = intval($_POST['image_pos_y'] ?? 50);

            // Handle photo upload
            $image_url = trim($_POST['existing_image_url'] ?? '');
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors = [
                        UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit (upload_max_filesize: ' . ini_get('upload_max_filesize') . ')',
                        UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                        UPLOAD_ERR_NO_TMP_DIR => 'No temp directory on server',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk — check server permissions',
                        UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension',
                    ];
                    $error = 'Upload error: ' . ($uploadErrors[$_FILES['photo']['error']] ?? 'Unknown error code ' . $_FILES['photo']['error']);
                } else {
                    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
                    finfo_close($finfo);

                    if (in_array($mime, $allowed)) {
                        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime];
                        $filename = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . time() . '.' . $ext;
                        $uploadDir = __DIR__ . '/../../assets/images/team/';

                        if (!is_dir($uploadDir)) {
                            if (!@mkdir($uploadDir, 0755, true)) {
                                $error = 'Cannot create upload directory. Please create assets/images/team/ on the server and set permissions to 755.';
                            }
                        }

                        if (!$error) {
                            if (!is_writable($uploadDir)) {
                                $error = 'Upload directory is not writable. Set permissions on assets/images/team/ to 755 or 775.';
                            } elseif (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                                if ($image_url && file_exists(__DIR__ . '/../../' . $image_url)) {
                                    @unlink(__DIR__ . '/../../' . $image_url);
                                }
                                $image_url = 'assets/images/team/' . $filename;
                            } else {
                                $error = 'Failed to move uploaded file. Check server permissions on assets/images/team/';
                            }
                        }
                    } else {
                        $error = 'Invalid file type (' . $mime . '). Use JPG, PNG, WebP, or GIF.';
                    }
                }
            }

            if (empty($name) || empty($role)) {
                $error = 'Name and role are required';
            }

            if (!$error) {
                if ($action === 'create') {
                    $stmt = $db->prepare("INSERT INTO team_members (name, role, bio, email, image_url, image_pos_x, image_pos_y, website_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $role, $bio, $email, $image_url, $image_pos_x, $image_pos_y, $website_url, $sort_order, $is_active]);
                    $message = 'Team member added';
                } else {
                    $stmt = $db->prepare("UPDATE team_members SET name=?, role=?, bio=?, email=?, image_url=?, image_pos_x=?, image_pos_y=?, website_url=?, sort_order=?, is_active=? WHERE id=?");
                    $stmt->execute([$name, $role, $bio, $email, $image_url, $image_pos_x, $image_pos_y, $website_url, $sort_order, $is_active, $id]);
                    $message = 'Team member updated';
                }
            }
        } elseif ($action === 'delete') {
            // Delete photo file
            $stmt = $db->prepare("SELECT image_url FROM team_members WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $delMember = $stmt->fetch();
            if ($delMember && $delMember['image_url'] && file_exists(__DIR__ . '/../../' . $delMember['image_url'])) {
                unlink(__DIR__ . '/../../' . $delMember['image_url']);
            }
            $db->prepare("DELETE FROM team_members WHERE id = ?")->execute([$_POST['id']]);
            $message = 'Team member deleted';
        }
    }
}

// Load edit item
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    if ($tab === 'product') {
        $stmt = $db->prepare("SELECT * FROM page_sections WHERE id = ?");
    } elseif ($tab === 'use-cases') {
        $stmt = $db->prepare("SELECT * FROM use_cases WHERE id = ?");
    } else {
        $stmt = $db->prepare("SELECT * FROM team_members WHERE id = ?");
    }
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}

// Load lists
$sections = $db->query("SELECT * FROM page_sections WHERE page_slug = 'product' ORDER BY sort_order, id")->fetchAll();
$useCases = $db->query("SELECT * FROM use_cases ORDER BY sort_order, id")->fetchAll();
$teamMembers = $db->query("SELECT * FROM team_members ORDER BY sort_order, id")->fetchAll();

$tabLabels = ['product' => 'ARIbrain', 'use-cases' => 'Use Cases', 'team' => 'Team'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages - Admin - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .admin-tabs { display: flex; gap: 4px; margin-bottom: 24px; background: var(--color-bg-alt); padding: 4px; border-radius: var(--radius-md); }
        .admin-tab { padding: 10px 20px; font-size: 0.9375rem; font-weight: 500; color: var(--color-text-muted); text-decoration: none; border-radius: var(--radius-sm); transition: all var(--transition); }
        .admin-tab:hover { color: var(--color-text); background: var(--color-bg); }
        .admin-tab.active { color: white; background: var(--color-primary); }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php $activePage = 'pages'; require __DIR__ . '/../../includes/admin/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Pages</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($currentAdmin['username']); ?></span>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-tabs">
                    <?php foreach ($tabLabels as $key => $label): ?>
                        <a href="index.php?page=admin/pages&tab=<?php echo $key; ?>" class="admin-tab <?php echo $tab === $key ? 'active' : ''; ?>"><?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>

                <?php if ($message): ?>
                    <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- ==================== PRODUCT TAB ==================== -->
                <?php if ($tab === 'product'): ?>
                <div class="admin-section">
                    <h2><?php echo $editItem ? 'Edit' : 'Add New'; ?> Section</h2>
                    <p style="color: var(--color-text-muted); font-size: 0.875rem; margin-bottom: 16px; margin-top: -12px;">Content sections displayed on the ARIbrain product page. HTML is supported in the content field.</p>
                    <form method="POST" class="admin-form-grid">
                        <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
                        <input type="hidden" name="tab" value="product">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                        <?php endif; ?>

                        <div class="admin-form-group full-width">
                            <label for="title">Section Title *</label>
                            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editItem['title'] ?? ''); ?>" placeholder="e.g. What is ARIbrain?, Key Features, How It Works">
                        </div>

                        <div class="admin-form-group full-width">
                            <label for="content">Content (HTML supported)</label>
                            <textarea id="content" name="content" rows="8"><?php echo htmlspecialchars($editItem['content'] ?? ''); ?></textarea>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" value="<?php echo $editItem['sort_order'] ?? 0; ?>" min="0">
                            </div>
                            <div class="admin-form-group">
                                <label for="is_active">
                                    <input type="checkbox" id="is_active" name="is_active" <?php echo ($editItem['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update' : 'Create'; ?></button>
                            <?php if ($editItem): ?>
                                <a href="index.php?page=admin/pages&tab=product" class="btn btn-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="admin-section">
                    <h2>Sections (<?php echo count($sections); ?>)</h2>
                    <?php if (empty($sections)): ?>
                        <p class="admin-empty">No sections yet. Add one above to build the ARIbrain page.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead><tr><th>Order</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($sections as $s): ?>
                                <tr<?php echo !$s['is_active'] ? ' class="admin-row-hidden"' : ''; ?>>
                                    <td><?php echo $s['sort_order']; ?></td>
                                    <td><?php echo htmlspecialchars($s['title']); ?></td>
                                    <td><?php echo $s['is_active'] ? '<span class="admin-badge admin-badge-success">Active</span>' : '<span class="admin-badge">Hidden</span>'; ?></td>
                                    <td class="admin-table-actions">
                                        <a href="index.php?page=admin/pages&tab=product&edit=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this section?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="product">
                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- ==================== USE CASES TAB ==================== -->
                <?php elseif ($tab === 'use-cases'): ?>
                <div class="admin-section">
                    <h2><?php echo $editItem ? 'Edit' : 'Add New'; ?> Use Case</h2>
                    <form method="POST" class="admin-form-grid">
                        <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
                        <input type="hidden" name="tab" value="use-cases">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                        <?php endif; ?>

                        <div class="admin-form-group full-width">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editItem['title'] ?? ''); ?>" placeholder="e.g. Task-based fMRI Analysis">
                        </div>

                        <div class="admin-form-group full-width">
                            <label for="summary">Summary (shown on listing)</label>
                            <textarea id="summary" name="summary" rows="2"><?php echo htmlspecialchars($editItem['summary'] ?? ''); ?></textarea>
                        </div>

                        <div class="admin-form-group full-width">
                            <label for="content">Full Content (HTML supported)</label>
                            <textarea id="content" name="content" rows="8"><?php echo htmlspecialchars($editItem['content'] ?? ''); ?></textarea>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="image_url">Image URL</label>
                                <input type="text" id="image_url" name="image_url" value="<?php echo htmlspecialchars($editItem['image_url'] ?? ''); ?>" placeholder="assets/images/example.png">
                            </div>
                            <div class="admin-form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" value="<?php echo $editItem['sort_order'] ?? 0; ?>" min="0">
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="is_featured">
                                    <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($editItem['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    Featured
                                </label>
                            </div>
                            <div class="admin-form-group">
                                <label for="is_active">
                                    <input type="checkbox" id="is_active" name="is_active" <?php echo ($editItem['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update' : 'Create'; ?></button>
                            <?php if ($editItem): ?>
                                <a href="index.php?page=admin/pages&tab=use-cases" class="btn btn-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="admin-section">
                    <h2>Use Cases (<?php echo count($useCases); ?>)</h2>
                    <?php if (empty($useCases)): ?>
                        <p class="admin-empty">No use cases yet.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead><tr><th>Order</th><th>Title</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($useCases as $uc): ?>
                                <tr<?php echo !$uc['is_active'] ? ' class="admin-row-hidden"' : ''; ?>>
                                    <td><?php echo $uc['sort_order']; ?></td>
                                    <td><?php echo htmlspecialchars($uc['title']); ?></td>
                                    <td><?php echo $uc['is_featured'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $uc['is_active'] ? '<span class="admin-badge admin-badge-success">Active</span>' : '<span class="admin-badge">Hidden</span>'; ?></td>
                                    <td class="admin-table-actions">
                                        <a href="index.php?page=admin/pages&tab=use-cases&edit=<?php echo $uc['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this use case?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="use-cases">
                                            <input type="hidden" name="id" value="<?php echo $uc['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- ==================== TEAM TAB ==================== -->
                <?php else: ?>
                <div class="admin-section">
                    <h2><?php echo $editItem ? 'Edit' : 'Add New'; ?> Team Member</h2>
                    <form method="POST" enctype="multipart/form-data" class="admin-form-grid">
                        <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
                        <input type="hidden" name="tab" value="team">
                        <input type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($editItem['image_url'] ?? ''); ?>">
                        <input type="hidden" name="image_pos_x" id="image_pos_x" value="<?php echo $editItem['image_pos_x'] ?? 50; ?>">
                        <input type="hidden" name="image_pos_y" id="image_pos_y" value="<?php echo $editItem['image_pos_y'] ?? 50; ?>">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                        <?php endif; ?>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Name *</label>
                                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($editItem['name'] ?? ''); ?>">
                            </div>
                            <div class="admin-form-group">
                                <label for="role">Role / Position *</label>
                                <input type="text" id="role" name="role" required value="<?php echo htmlspecialchars($editItem['role'] ?? ''); ?>" placeholder="e.g. Principal Investigator, PhD Researcher">
                            </div>
                        </div>

                        <div class="admin-form-group full-width">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($editItem['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($editItem['email'] ?? ''); ?>">
                            </div>
                            <div class="admin-form-group">
                                <label for="website_url">Website URL</label>
                                <input type="url" id="website_url" name="website_url" value="<?php echo htmlspecialchars($editItem['website_url'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="admin-form-group full-width">
                            <label>Photo</label>
                            <div style="display: flex; gap: 28px; align-items: flex-start; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 200px;">
                                    <?php if ($editItem && ($editItem['image_url'] ?? '')): ?>
                                    <div id="current-photo-info" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 12px;">
                                        <img src="<?php echo htmlspecialchars($editItem['image_url']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; object-position: <?php echo ($editItem['image_pos_x'] ?? 50); ?>% <?php echo ($editItem['image_pos_y'] ?? 50); ?>%;">
                                        <div>
                                            <p style="margin: 0; font-size: 0.875rem; font-weight: 500; color: #16a34a;">Current photo</p>
                                            <p style="margin: 0; font-size: 0.75rem; color: #64748b;"><?php echo basename($editItem['image_url']); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                                    <label for="photo-input" style="display: inline-flex; align-items: center; gap: 10px; padding: 10px 16px; border: 1px solid var(--color-border); border-radius: 8px; cursor: pointer; transition: all 0.2s; font-size: 0.875rem; color: var(--color-text-muted);" onmouseover="this.style.borderColor='var(--color-primary)';this.style.background='#f8fafc'" onmouseout="this.style.borderColor='var(--color-border)';this.style.background='transparent'">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        <span id="upload-label"><?php echo ($editItem && ($editItem['image_url'] ?? '')) ? 'Replace photo...' : 'Choose photo...'; ?></span>
                                    </label>
                                    <span id="compress-status" style="font-size: 0.75rem; color: var(--color-text-light); margin-top: 4px; display: block;">JPG, PNG, WebP, or GIF — auto-compressed if over 2MB</span>
                                    <input type="file" id="photo-input" accept="image/jpeg,image/png,image/webp,image/gif" onchange="compressAndPreview(this)" style="display: none;">
                                    <input type="file" id="photo" name="photo" style="display: none;">
                                    <canvas id="compress-canvas" style="display: none;"></canvas>
                                </div>
                                <div id="photo-positioner" style="display: <?php echo ($editItem['image_url'] ?? '') ? 'flex' : 'none'; ?>; flex-direction: column; align-items: center;">
                                    <p style="font-size: 0.8125rem; font-weight: 500; margin-bottom: 8px; color: var(--color-text);">Click to center face</p>
                                    <div id="pos-container" style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden; border: 3px solid var(--color-primary); cursor: crosshair; position: relative; background: #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                        <?php if ($editItem['image_url'] ?? ''): ?>
                                        <img id="pos-image" src="<?php echo htmlspecialchars($editItem['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: <?php echo ($editItem['image_pos_x'] ?? 50); ?>% <?php echo ($editItem['image_pos_y'] ?? 50); ?>%; pointer-events: none;">
                                        <?php else: ?>
                                        <img id="pos-image" src="" style="width: 100%; height: 100%; object-fit: cover; object-position: 50% 50%; pointer-events: none; display: none;">
                                        <?php endif; ?>
                                    </div>
                                    <p id="pos-label" style="font-size: 0.75rem; color: var(--color-text-light); margin-top: 6px;">Position: <?php echo ($editItem['image_pos_x'] ?? 50); ?>% / <?php echo ($editItem['image_pos_y'] ?? 50); ?>%</p>
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" value="<?php echo $editItem['sort_order'] ?? 0; ?>" min="0">
                            </div>
                            <div class="admin-form-group">
                                <label for="is_active">
                                    <input type="checkbox" id="is_active" name="is_active" <?php echo ($editItem['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update' : 'Create'; ?></button>
                            <?php if ($editItem): ?>
                                <a href="index.php?page=admin/pages&tab=team" class="btn btn-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="admin-section">
                    <h2>Team Members (<?php echo count($teamMembers); ?>)</h2>
                    <?php if (empty($teamMembers)): ?>
                        <p class="admin-empty">No team members yet.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead><tr><th style="width:50px"></th><th>Name</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($teamMembers as $tm): ?>
                                <tr<?php echo !$tm['is_active'] ? ' class="admin-row-hidden"' : ''; ?>>
                                    <td>
                                        <?php if ($tm['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($tm['image_url']); ?>" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; object-position: <?php echo $tm['image_pos_x'] ?? 50; ?>% <?php echo $tm['image_pos_y'] ?? 50; ?>%;">
                                        <?php else: ?>
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #6bcf8e, #4cb872); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;"><?php echo strtoupper(substr($tm['name'], 0, 1)); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($tm['name']); ?></td>
                                    <td><?php echo htmlspecialchars($tm['role']); ?></td>
                                    <td><?php echo $tm['is_active'] ? '<span class="admin-badge admin-badge-success">Active</span>' : '<span class="admin-badge">Hidden</span>'; ?></td>
                                    <td class="admin-table-actions">
                                        <a href="index.php?page=admin/pages&tab=team&edit=<?php echo $tm['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this team member?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="team">
                                            <input type="hidden" name="id" value="<?php echo $tm['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
    var MAX_SIZE = 1.9 * 1024 * 1024; // 1.9MB to stay safely under 2MB

    function compressAndPreview(input) {
        if (!input.files || !input.files[0]) return;
        var file = input.files[0];
        var status = document.getElementById('compress-status');

        if (file.size <= MAX_SIZE) {
            // Small enough — use as-is
            setPhotoFile(file);
            showPreview(file);
            status.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
            status.style.color = 'var(--color-text-light)';
            return;
        }

        // Need to compress
        status.textContent = 'Compressing ' + file.name + '...';
        status.style.color = '#f59e0b';

        var img = new Image();
        img.onload = function() {
            var canvas = document.getElementById('compress-canvas');
            var maxDim = 1200;
            var w = img.width, h = img.height;
            if (w > maxDim || h > maxDim) {
                if (w > h) { h = Math.round(h * maxDim / w); w = maxDim; }
                else { w = Math.round(w * maxDim / h); h = maxDim; }
            }
            canvas.width = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);

            // Try decreasing quality until under limit
            var quality = 0.85;
            (function tryCompress() {
                canvas.toBlob(function(blob) {
                    if (blob.size > MAX_SIZE && quality > 0.3) {
                        quality -= 0.1;
                        tryCompress();
                    } else {
                        var compressed = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' });
                        setPhotoFile(compressed);
                        showPreview(compressed);
                        var pct = ((1 - compressed.size / file.size) * 100).toFixed(0);
                        status.textContent = file.name + ' — compressed ' + pct + '% (' + (compressed.size / 1024).toFixed(0) + ' KB)';
                        status.style.color = '#16a34a';
                    }
                }, 'image/jpeg', quality);
            })();
        };
        img.src = URL.createObjectURL(file);
    }

    function setPhotoFile(file) {
        var dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('photo').files = dt.files;
    }

    function showPreview(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('pos-image');
            img.src = e.target.result;
            img.style.display = 'block';
            img.style.objectPosition = '50% 50%';
            document.getElementById('photo-positioner').style.display = 'flex';
            document.getElementById('image_pos_x').value = 50;
            document.getElementById('image_pos_y').value = 50;
            document.getElementById('pos-label').textContent = 'Position: 50% / 50%';
            document.getElementById('upload-label').textContent = file.name;
            var info = document.getElementById('current-photo-info');
            if (info) info.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    var posContainer = document.getElementById('pos-container');
    if (posContainer) {
        posContainer.addEventListener('click', function(e) {
            var rect = this.getBoundingClientRect();
            var x = Math.round(((e.clientX - rect.left) / rect.width) * 100);
            var y = Math.round(((e.clientY - rect.top) / rect.height) * 100);
            x = Math.max(0, Math.min(100, x));
            y = Math.max(0, Math.min(100, y));

            var img = document.getElementById('pos-image');
            img.style.objectPosition = x + '% ' + y + '%';
            document.getElementById('image_pos_x').value = x;
            document.getElementById('image_pos_y').value = y;
            document.getElementById('pos-label').textContent = 'Position: ' + x + '% / ' + y + '%';
        });
    }
    </script>
</body>
</html>
