<?php
$pageTitle = 'What is ARIbrain?';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/sanitize.php';
$db = Database::getInstance()->getConnection();
$sections = $db->query("SELECT * FROM page_sections WHERE page_slug = 'product' AND is_active = 1 ORDER BY sort_order, id")->fetchAll();
?>

<div class="page-header">
    <div class="container">
        <h1>What is ARIbrain?</h1>
        <p>Advanced neuroimaging analysis for precise fMRI interpretation</p>
    </div>
</div>

<?php if (empty($sections)): ?>
<div class="container">
    <div class="placeholder-content">
        <div class="icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
            </svg>
        </div>
        <h2>Content Coming Soon</h2>
        <p>We're working on detailed documentation about ARIbrain's features, the spatial specificity paradox it solves, and how it can enhance your neuroimaging research.</p>
        <a href="<?php echo GITHUB_URL; ?>" class="btn btn-primary" target="_blank" rel="noopener">View README on GitHub</a>
    </div>
</div>
<?php else: ?>
<?php foreach ($sections as $i => $section): ?>
<section class="section <?php echo $i % 2 === 1 ? 'section-alt' : ''; ?>">
    <div class="container">
        <div class="content-block">
            <h2><?php echo htmlspecialchars($section['title']); ?></h2>
            <div class="content-body">
                <?php echo sanitize_html($section['content']); ?>
            </div>
        </div>
    </div>
</section>
<?php endforeach; ?>
<?php endif; ?>
