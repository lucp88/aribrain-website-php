<?php
$pageTitle = 'News & Updates';
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$posts = $db->query("SELECT * FROM news_posts WHERE is_published = 1 ORDER BY published_at DESC, created_at DESC")->fetchAll();
?>

<div class="page-header">
    <div class="container">
        <h1>News & Updates</h1>
        <p>Latest news, releases, and tutorials from the ARIbrain team</p>
    </div>
</div>

<?php if (empty($posts)): ?>
<div class="container">
    <div class="placeholder-content">
        <div class="icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
                <path d="M18 14h-8"/>
                <path d="M15 18h-5"/>
                <path d="M10 6h8v4h-8V6z"/>
            </svg>
        </div>
        <h2>No News Yet</h2>
        <p>Stay tuned for release announcements, tutorials, conference highlights, and research updates from the ARIbrain team.</p>
        <a href="<?php echo GITHUB_URL; ?>/releases" class="btn btn-primary" target="_blank" rel="noopener">View Releases on GitHub</a>
    </div>
</div>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="news-list">
            <?php foreach ($posts as $post): ?>
            <article class="news-card <?php echo $post['is_featured'] ? 'featured' : ''; ?>">
                <div class="news-meta">
                    <?php if ($post['is_featured']): ?>
                        <span class="news-badge">Featured</span>
                    <?php endif; ?>
                    <time datetime="<?php echo $post['published_at']; ?>">
                        <?php echo $post['published_at'] ? date('F j, Y', strtotime($post['published_at'])) : ''; ?>
                    </time>
                    <?php if ($post['author_name']): ?>
                        <span class="news-author">by <?php echo htmlspecialchars($post['author_name']); ?></span>
                    <?php endif; ?>
                </div>
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <?php if ($post['excerpt']): ?>
                    <p class="news-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                <?php endif; ?>
                <div class="news-body"><?php echo $post['content']; ?></div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
