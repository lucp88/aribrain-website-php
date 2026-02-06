<?php 
$pageTitle = 'Community Forum';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_question') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $author_name = trim($_POST['author_name'] ?? '');
        $author_email = trim($_POST['author_email'] ?? '');
        $tags = $_POST['tags'] ?? [];
        
        if (empty($title) || empty($content) || empty($author_name)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("INSERT INTO forum_questions (title, content, author_name, author_email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $content, $author_name, $author_email]);
                $questionId = $db->lastInsertId();
                
                if (!empty($tags)) {
                    $tagStmt = $db->prepare("INSERT INTO forum_question_tags (question_id, tag_id) VALUES (?, ?)");
                    foreach ($tags as $tagId) {
                        $tagStmt->execute([$questionId, $tagId]);
                    }
                }
                
                $db->commit();
                $message = 'Your question has been posted successfully!';
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Failed to post question. Please try again.';
            }
        }
    } elseif ($_POST['action'] === 'reply') {
        $questionId = intval($_POST['question_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $author_name = trim($_POST['author_name'] ?? '');
        $author_email = trim($_POST['author_email'] ?? '');
        
        if (empty($content) || empty($author_name) || !$questionId) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $db->prepare("INSERT INTO forum_replies (question_id, content, author_name, author_email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$questionId, $content, $author_name, $author_email]);
            $message = 'Your reply has been posted!';
        }
    }
}

$tags = [];
$questions = [];
$selectedQuestion = null;
$replies = [];

if ($db) {
    $tags = $db->query("SELECT * FROM forum_tags ORDER BY name")->fetchAll();
    
    $filterTag = isset($_GET['tag']) ? intval($_GET['tag']) : null;
    $filterFeatured = isset($_GET['featured']);
    $sort = $_GET['sort'] ?? 'newest';
    
    $orderBy = match($sort) {
        'oldest' => 'q.created_at ASC',
        'topic' => 'MIN(t.name) ASC, q.created_at DESC',
        default => 'q.is_featured DESC, q.created_at DESC'
    };
    
    if (isset($_GET['q'])) {
        $qId = intval($_GET['q']);
        $stmt = $db->prepare("SELECT q.*, GROUP_CONCAT(t.id) as tag_ids, GROUP_CONCAT(t.name) as tag_names, GROUP_CONCAT(t.color) as tag_colors 
                              FROM forum_questions q 
                              LEFT JOIN forum_question_tags qt ON q.id = qt.question_id 
                              LEFT JOIN forum_tags t ON qt.tag_id = t.id 
                              WHERE q.id = ? AND q.is_approved = 1 
                              GROUP BY q.id");
        $stmt->execute([$qId]);
        $selectedQuestion = $stmt->fetch();
        
        if ($selectedQuestion) {
            $db->prepare("UPDATE forum_questions SET views = views + 1 WHERE id = ?")->execute([$qId]);
            $stmt = $db->prepare("SELECT r.*, a.username as admin_username FROM forum_replies r LEFT JOIN admins a ON r.admin_id = a.id WHERE r.question_id = ? AND r.is_approved = 1 ORDER BY r.created_at ASC");
            $stmt->execute([$qId]);
            $replies = $stmt->fetchAll();
        }
    } else {
        $whereConditions = ['q.is_approved = 1'];
        $params = [];
        
        if ($filterFeatured) {
            $whereConditions[] = 'q.is_featured = 1';
        }
        
        if ($filterTag) {
            $whereConditions[] = 'q.id IN (SELECT question_id FROM forum_question_tags WHERE tag_id = ?)';
            $params[] = $filterTag;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $sql = "SELECT q.*, GROUP_CONCAT(t.id) as tag_ids, GROUP_CONCAT(t.name) as tag_names, GROUP_CONCAT(t.color) as tag_colors,
                (SELECT COUNT(*) FROM forum_replies WHERE question_id = q.id AND is_approved = 1) as reply_count
                FROM forum_questions q 
                LEFT JOIN forum_question_tags qt ON q.id = qt.question_id 
                LEFT JOIN forum_tags t ON qt.tag_id = t.id 
                WHERE {$whereClause}
                GROUP BY q.id 
                ORDER BY {$orderBy}";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $questions = $stmt->fetchAll();
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}

function renderQuestionTags($tagIds, $tagNames, $tagColors, $asLinks = true) {
    if (empty($tagNames)) return '';
    $ids = explode(',', $tagIds);
    $names = explode(',', $tagNames);
    $colors = explode(',', $tagColors);
    $html = '<div class="forum-tags">';
    for ($i = 0; $i < count($names); $i++) {
        $style = 'background-color: ' . htmlspecialchars($colors[$i]) . '20; color: ' . htmlspecialchars($colors[$i]) . '; border-color: ' . htmlspecialchars($colors[$i]) . '40;';
        if ($asLinks) {
            $html .= '<a href="index.php?page=forum&tag=' . $ids[$i] . '" class="forum-tag" style="' . $style . '">' . htmlspecialchars($names[$i]) . '</a>';
        } else {
            $html .= '<span class="forum-tag" style="' . $style . '">' . htmlspecialchars($names[$i]) . '</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>

<div class="page-header">
    <div class="container">
        <h1>Community Forum</h1>
        <p>Ask questions, share knowledge, and connect with the ARIbrain community</p>
    </div>
</div>

<div class="container">
    <?php if ($message): ?>
        <div class="forum-alert forum-alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="forum-alert forum-alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($selectedQuestion): ?>
    <div class="forum-question-detail">
        <a href="index.php?page=forum" class="forum-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to all questions
        </a>
        
        <article class="forum-question-full">
            <header class="forum-question-header">
                <h2><?php echo htmlspecialchars($selectedQuestion['title']); ?></h2>
                <?php echo renderQuestionTags($selectedQuestion['tag_ids'], $selectedQuestion['tag_names'], $selectedQuestion['tag_colors']); ?>
            </header>
            
            <div class="forum-question-body">
                <?php echo nl2br(htmlspecialchars($selectedQuestion['content'])); ?>
            </div>
            
            <footer class="forum-question-footer">
                <div class="forum-author">
                    <div class="forum-avatar"><?php echo strtoupper(substr($selectedQuestion['author_name'], 0, 1)); ?></div>
                    <div class="forum-author-info">
                        <span class="forum-author-name"><?php echo htmlspecialchars($selectedQuestion['author_name']); ?></span>
                        <span class="forum-timestamp">Asked <?php echo timeAgo($selectedQuestion['created_at']); ?></span>
                    </div>
                </div>
                <div class="forum-stats">
                    <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> <?php echo $selectedQuestion['views']; ?> views</span>
                </div>
            </footer>
        </article>
        
        <section class="forum-replies-section">
            <h3><?php echo count($replies); ?> <?php echo count($replies) === 1 ? 'Reply' : 'Replies'; ?></h3>
            
            <?php if (empty($replies)): ?>
                <p class="forum-no-replies">No replies yet. Be the first to help!</p>
            <?php else: ?>
                <?php foreach ($replies as $reply): ?>
                <div class="forum-reply <?php echo $reply['is_admin_reply'] ? 'forum-reply-admin' : ''; ?>">
                    <div class="forum-reply-body">
                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                    </div>
                    <footer class="forum-reply-footer">
                        <div class="forum-author">
                            <div class="forum-avatar <?php echo $reply['is_admin_reply'] ? 'forum-avatar-admin' : ''; ?>">
                                <?php echo strtoupper(substr($reply['is_admin_reply'] ? $reply['admin_username'] : $reply['author_name'], 0, 1)); ?>
                            </div>
                            <div class="forum-author-info">
                                <span class="forum-author-name">
                                    <?php echo htmlspecialchars($reply['is_admin_reply'] ? $reply['admin_username'] : $reply['author_name']); ?>
                                    <?php if ($reply['is_admin_reply']): ?>
                                        <span class="forum-admin-badge">Team</span>
                                    <?php endif; ?>
                                </span>
                                <span class="forum-timestamp"><?php echo timeAgo($reply['created_at']); ?></span>
                            </div>
                        </div>
                    </footer>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="forum-reply-form">
                <h4>Post a Reply</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="reply">
                    <input type="hidden" name="question_id" value="<?php echo $selectedQuestion['id']; ?>">
                    
                    <div class="forum-form-row">
                        <div class="forum-form-group">
                            <label for="reply_name">Your Name *</label>
                            <input type="text" id="reply_name" name="author_name" required>
                        </div>
                        <div class="forum-form-group">
                            <label for="reply_email">Email (optional)</label>
                            <input type="email" id="reply_email" name="author_email">
                        </div>
                    </div>
                    
                    <div class="forum-form-group">
                        <label for="reply_content">Your Reply *</label>
                        <textarea id="reply_content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Post Reply</button>
                </form>
            </div>
        </section>
    </div>
    
    <?php else: ?>
    <div class="forum-layout">
        <aside class="forum-sidebar">
            <button class="btn btn-primary btn-lg forum-ask-btn" onclick="openQuestionModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Ask a Question
            </button>
            
            <div class="forum-tags-filter">
                <h3>Filter by Topic</h3>
                <a href="index.php?page=forum" class="forum-tag-filter <?php echo (!isset($_GET['tag']) && !$filterFeatured) ? 'active' : ''; ?>">All Topics</a>
                <a href="index.php?page=forum&featured" class="forum-tag-filter <?php echo $filterFeatured ? 'active' : ''; ?>" style="--tag-color: #f59e0b">Featured</a>
                <?php foreach ($tags as $tag): ?>
                <a href="index.php?page=forum&tag=<?php echo $tag['id']; ?>" class="forum-tag-filter <?php echo (isset($_GET['tag']) && $_GET['tag'] == $tag['id']) ? 'active' : ''; ?>" style="--tag-color: <?php echo htmlspecialchars($tag['color']); ?>">
                    <?php echo htmlspecialchars($tag['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </aside>
        
        <main class="forum-main">
            <div class="forum-header">
                <h2>
                    <?php if ($filterFeatured): ?>
                        Featured Questions
                    <?php elseif (isset($_GET['tag'])): ?>
                        <?php 
                        $currentTag = array_filter($tags, fn($t) => $t['id'] == $_GET['tag']);
                        echo htmlspecialchars(reset($currentTag)['name'] ?? 'Questions'); 
                        ?>
                    <?php else: ?>
                        All Questions
                    <?php endif; ?>
                </h2>
                <div class="forum-header-right">
                    <select class="forum-sort-select" onchange="window.location.href=this.value">
                        <option value="index.php?page=forum<?php echo $filterTag ? '&tag='.$filterTag : ''; ?><?php echo $filterFeatured ? '&featured' : ''; ?>&sort=newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="index.php?page=forum<?php echo $filterTag ? '&tag='.$filterTag : ''; ?><?php echo $filterFeatured ? '&featured' : ''; ?>&sort=oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                        <option value="index.php?page=forum<?php echo $filterTag ? '&tag='.$filterTag : ''; ?><?php echo $filterFeatured ? '&featured' : ''; ?>&sort=topic" <?php echo $sort === 'topic' ? 'selected' : ''; ?>>Topic</option>
                    </select>
                    <span class="forum-count"><?php echo count($questions); ?> questions</span>
                </div>
            </div>
            
            <?php if (empty($questions)): ?>
            <div class="forum-empty">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <h3>No questions yet</h3>
                <p>Be the first to ask a question!</p>
                <button class="btn btn-primary" onclick="openQuestionModal()">Ask a Question</button>
            </div>
            <?php else: ?>
            <div class="forum-questions-list">
                <?php foreach ($questions as $question): ?>
                <a href="index.php?page=forum&q=<?php echo $question['id']; ?>" class="forum-question-card <?php echo $question['is_featured'] ? 'forum-question-featured' : ''; ?>">
                    <div class="forum-question-title">
                        <?php if ($question['is_featured']): ?>
                            <span class="forum-featured-badge">Featured</span>
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($question['title']); ?></h3>
                        <?php if ($question['is_answered']): ?>
                            <span class="forum-answered-badge">Answered</span>
                        <?php endif; ?>
                    </div>
                    <p class="forum-question-excerpt"><?php echo htmlspecialchars(substr($question['content'], 0, 150)) . (strlen($question['content']) > 150 ? '...' : ''); ?></p>
                    <div class="forum-question-footer-meta">
                        <?php echo renderQuestionTags($question['tag_ids'], $question['tag_names'], $question['tag_colors'], false); ?>
                        <div class="forum-meta-info">
                            <span><?php echo $question['reply_count']; ?> replies</span>
                            <span><?php echo $question['views']; ?> views</span>
                            <span><?php echo htmlspecialchars($question['author_name']); ?></span>
                            <span class="forum-timestamp"><?php echo timeAgo($question['created_at']); ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
    <?php endif; ?>
</div>

<div id="questionModal" class="forum-modal">
    <div class="forum-modal-content">
        <div class="forum-modal-header">
            <h2>Ask a Question</h2>
            <button class="forum-modal-close" onclick="closeQuestionModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <form method="POST" class="forum-modal-form">
            <input type="hidden" name="action" value="create_question">
            
            <div class="forum-form-group">
                <label for="question_title">Title *</label>
                <input type="text" id="question_title" name="title" placeholder="What's your question?" required>
            </div>
            
            <div class="forum-form-group">
                <label for="question_content">Description *</label>
                <textarea id="question_content" name="content" rows="5" placeholder="Provide details about your question..." required></textarea>
            </div>
            
            <div class="forum-form-group">
                <label>Topics</label>
                <div class="forum-tag-select">
                    <?php foreach ($tags as $tag): ?>
                    <label class="forum-tag-checkbox" style="--tag-color: <?php echo htmlspecialchars($tag['color']); ?>">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
                        <span><?php echo htmlspecialchars($tag['name']); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="forum-form-row">
                <div class="forum-form-group">
                    <label for="question_name">Your Name *</label>
                    <input type="text" id="question_name" name="author_name" required>
                </div>
                <div class="forum-form-group">
                    <label for="question_email">Email (optional)</label>
                    <input type="email" id="question_email" name="author_email" placeholder="For notifications">
                </div>
            </div>
            
            <div class="forum-modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeQuestionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Post Question</button>
            </div>
        </form>
    </div>
</div>

<script>
function openQuestionModal() {
    document.getElementById('questionModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeQuestionModal() {
    document.getElementById('questionModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('questionModal').addEventListener('click', function(e) {
    if (e.target === this) closeQuestionModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeQuestionModal();
});
</script>
