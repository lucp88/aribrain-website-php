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
        $authors = trim($_POST['authors'] ?? '');
        $pub_type = $_POST['pub_type'] ?? 'paper';
        $journal = trim($_POST['journal'] ?? '');
        $year = intval($_POST['year'] ?? date('Y'));
        $pub_date = $_POST['pub_date'] ?? null;
        $location = trim($_POST['location'] ?? '');
        $conference = trim($_POST['conference'] ?? '');
        $presentation_type = trim($_POST['presentation_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $topics = trim($_POST['topics'] ?? '');
        $doi = trim($_POST['doi'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $github_url = trim($_POST['github_url'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        if (empty($title) || empty($authors)) {
            $error = 'Title and authors are required';
        } else {
            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO publications (title, authors, pub_type, journal, year, pub_date, location, conference, presentation_type, description, topics, doi, url, github_url, video_url, is_featured, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $authors, $pub_type, $journal, $year, $pub_date ?: null, $location, $conference, $presentation_type, $description, $topics, $doi, $url, $github_url, $video_url, $is_featured, $currentAdmin['id']]);
                $message = 'Publication created successfully';
            } else {
                $stmt = $db->prepare("UPDATE publications SET title=?, authors=?, pub_type=?, journal=?, year=?, pub_date=?, location=?, conference=?, presentation_type=?, description=?, topics=?, doi=?, url=?, github_url=?, video_url=?, is_featured=? WHERE id=?");
                $stmt->execute([$title, $authors, $pub_type, $journal, $year, $pub_date ?: null, $location, $conference, $presentation_type, $description, $topics, $doi, $url, $github_url, $video_url, $is_featured, $id]);
                $message = 'Publication updated successfully';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM publications WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Publication deleted successfully';
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM publications WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$talks = $db->query("SELECT * FROM publications WHERE pub_type = 'talk' ORDER BY pub_date DESC, year DESC")->fetchAll();
$papers = $db->query("SELECT * FROM publications WHERE pub_type = 'paper' ORDER BY year DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publications - Admin - <?php echo SITE_NAME; ?></title>
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
                <a href="index.php?page=admin/publications" class="admin-nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Publications
                </a>
                <a href="index.php?page=admin/forum" class="admin-nav-item">
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
                <h1>Publications</h1>
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
                    <h2><?php echo $editItem ? 'Edit' : 'Add New'; ?> Publication / Talk</h2>
                    <form method="POST" class="admin-form-grid">
                        <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="pub_type">Type *</label>
                                <select id="pub_type" name="pub_type" required onchange="toggleFields()">
                                    <option value="talk" <?php echo ($editItem['pub_type'] ?? '') === 'talk' ? 'selected' : ''; ?>>Talk / Presentation</option>
                                    <option value="paper" <?php echo ($editItem['pub_type'] ?? '') === 'paper' ? 'selected' : ''; ?>>Paper / Publication</option>
                                </select>
                            </div>
                            <div class="admin-form-group">
                                <label for="is_featured">
                                    <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($editItem['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    Featured
                                </label>
                            </div>
                        </div>
                        
                        <div class="admin-form-group full-width">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editItem['title'] ?? ''); ?>">
                        </div>
                        
                        <div class="admin-form-group full-width">
                            <label for="authors">Authors * (use <strong>Name</strong> for highlighting)</label>
                            <input type="text" id="authors" name="authors" required value="<?php echo htmlspecialchars($editItem['authors'] ?? ''); ?>" placeholder="**Wouter Weeda**, Jelle Goeman, Amanda Mejia">
                        </div>
                        
                        <div class="admin-form-group full-width">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="talk-fields">
                            <div class="admin-form-row">
                                <div class="admin-form-group">
                                    <label for="conference">Conference / Event</label>
                                    <input type="text" id="conference" name="conference" value="<?php echo htmlspecialchars($editItem['conference'] ?? ''); ?>" placeholder="OHBM 2025">
                                </div>
                                <div class="admin-form-group">
                                    <label for="presentation_type">Presentation Type</label>
                                    <input type="text" id="presentation_type" name="presentation_type" value="<?php echo htmlspecialchars($editItem['presentation_type'] ?? ''); ?>" placeholder="Educational Course, Software Demo, Poster">
                                </div>
                            </div>
                            
                            <div class="admin-form-row">
                                <div class="admin-form-group">
                                    <label for="pub_date">Date</label>
                                    <input type="date" id="pub_date" name="pub_date" value="<?php echo htmlspecialchars($editItem['pub_date'] ?? ''); ?>">
                                </div>
                                <div class="admin-form-group">
                                    <label for="location">Location</label>
                                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($editItem['location'] ?? ''); ?>" placeholder="Brisbane, Australia">
                                </div>
                            </div>
                        </div>
                        
                        <div class="paper-fields">
                            <div class="admin-form-row">
                                <div class="admin-form-group">
                                    <label for="journal">Journal</label>
                                    <input type="text" id="journal" name="journal" value="<?php echo htmlspecialchars($editItem['journal'] ?? ''); ?>" placeholder="NeuroImage">
                                </div>
                                <div class="admin-form-group">
                                    <label for="year">Year</label>
                                    <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($editItem['year'] ?? date('Y')); ?>" min="1900" max="2100">
                                </div>
                            </div>
                            
                            <div class="admin-form-group full-width">
                                <label for="doi">DOI</label>
                                <input type="text" id="doi" name="doi" value="<?php echo htmlspecialchars($editItem['doi'] ?? ''); ?>" placeholder="10.1016/j.neuroimage.2018.07.060">
                            </div>
                        </div>
                        
                        <div class="admin-form-group full-width">
                            <label for="topics">Topics (comma-separated)</label>
                            <input type="text" id="topics" name="topics" value="<?php echo htmlspecialchars($editItem['topics'] ?? ''); ?>" placeholder="ARIbrain, TDP Analysis, fMRI">
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="url">URL / Materials Link</label>
                                <input type="url" id="url" name="url" value="<?php echo htmlspecialchars($editItem['url'] ?? ''); ?>">
                            </div>
                            <div class="admin-form-group">
                                <label for="github_url">GitHub URL</label>
                                <input type="url" id="github_url" name="github_url" value="<?php echo htmlspecialchars($editItem['github_url'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="admin-form-group full-width">
                            <label for="video_url">Video URL (YouTube/Vimeo)</label>
                            <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($editItem['video_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update' : 'Create'; ?></button>
                            <?php if ($editItem): ?>
                                <a href="index.php?page=admin/publications" class="btn btn-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <div class="admin-section">
                    <h2>Talks & Presentations (<?php echo count($talks); ?>)</h2>
                    <?php if (empty($talks)): ?>
                        <p class="admin-empty">No talks yet. Add one above.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Conference</th>
                                    <th>Date</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($talks as $talk): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($talk['title']); ?></td>
                                    <td><?php echo htmlspecialchars($talk['conference']); ?></td>
                                    <td><?php echo $talk['pub_date'] ? date('M j, Y', strtotime($talk['pub_date'])) : $talk['year']; ?></td>
                                    <td><?php echo $talk['is_featured'] ? 'Yes' : 'No'; ?></td>
                                    <td class="admin-table-actions">
                                        <a href="index.php?page=admin/publications&edit=<?php echo $talk['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this talk?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $talk['id']; ?>">
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
                    <h2>Papers & Publications (<?php echo count($papers); ?>)</h2>
                    <?php if (empty($papers)): ?>
                        <p class="admin-empty">No papers yet. Add one above.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Journal</th>
                                    <th>Year</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($papers as $paper): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($paper['title']); ?></td>
                                    <td><?php echo htmlspecialchars($paper['journal']); ?></td>
                                    <td><?php echo $paper['year']; ?></td>
                                    <td><?php echo $paper['is_featured'] ? 'Yes' : 'No'; ?></td>
                                    <td class="admin-table-actions">
                                        <a href="index.php?page=admin/publications&edit=<?php echo $paper['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this paper?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $paper['id']; ?>">
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
    
    <script>
    function toggleFields() {
        const pubType = document.getElementById('pub_type').value;
        document.querySelectorAll('.talk-fields').forEach(el => el.style.display = pubType === 'talk' ? 'block' : 'none');
        document.querySelectorAll('.paper-fields').forEach(el => el.style.display = pubType === 'paper' ? 'block' : 'none');
    }
    toggleFields();
    </script>
</body>
</html>
