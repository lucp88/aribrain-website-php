<?php
$pageTitle = 'Use Cases & Demonstrations';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/sanitize.php';
$db = Database::getInstance()->getConnection();
$useCases = $db->query("SELECT * FROM use_cases WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll();
?>

<div class="page-header">
    <div class="container">
        <h1>Use Cases & Demonstrations</h1>
        <p>See ARIbrain in action with real-world examples</p>
    </div>
</div>

<?php if (empty($useCases)): ?>
<div class="container">
    <div class="placeholder-content">
        <div class="icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                <polyline points="2 17 12 22 22 17"/>
                <polyline points="2 12 12 17 22 12"/>
            </svg>
        </div>
        <h2>Content Coming Soon</h2>
        <p>We're preparing detailed case studies showcasing ARIbrain with real fMRI datasets, neuroimaging pipeline integrations, and result visualizations.</p>
        <a href="<?php echo GITHUB_URL; ?>" class="btn btn-primary" target="_blank" rel="noopener">View Examples on GitHub</a>
    </div>
</div>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="use-cases-grid">
            <?php foreach ($useCases as $uc): ?>
            <div class="use-case-card <?php echo $uc['is_featured'] ? 'featured' : ''; ?>">
                <?php if ($uc['image_url']): ?>
                    <div class="use-case-image">
                        <img src="<?php echo htmlspecialchars($uc['image_url']); ?>" alt="<?php echo htmlspecialchars($uc['title']); ?>">
                    </div>
                <?php endif; ?>
                <div class="use-case-content">
                    <?php if ($uc['is_featured']): ?>
                        <span class="use-case-badge">Featured</span>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($uc['title']); ?></h3>
                    <?php if ($uc['summary']): ?>
                        <p class="use-case-summary"><?php echo htmlspecialchars($uc['summary']); ?></p>
                    <?php endif; ?>
                    <?php if ($uc['content']): ?>
                        <div class="use-case-body"><?php echo sanitize_html($uc['content']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
