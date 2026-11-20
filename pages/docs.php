<?php
// Content comes from assets/docs/USER_GUIDE.md — edit that file, not this one.
require_once __DIR__ . '/../includes/docs-renderer.php';

$guide = render_user_guide(__DIR__ . '/../assets/docs/USER_GUIDE.md');
$pageTitle = 'User Guide';
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($guide['title'] ?? 'ARIbrain User Guide'); ?></h1>
        <?php if (!empty($guide['intro_html'])): ?>
            <p><?php echo $guide['intro_html']; ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="container">
<?php if (!$guide): ?>
    <div class="docs-section">
        <p>The user guide is temporarily unavailable. Please see the <a href="<?php echo GITHUB_URL; ?>" target="_blank" rel="noopener">README on GitHub</a>.</p>
    </div>
<?php else: ?>
    <div class="docs-layout">
        <nav class="docs-nav">
            <h3>Quick Navigation</h3>
            <?php foreach ($guide['sections'] as $section): ?>
                <a href="#<?php echo htmlspecialchars($section['id']); ?>" class="docs-nav-item"><?php echo htmlspecialchars($section['title']); ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="docs-content">
            <?php foreach ($guide['sections'] as $i => $section): ?>
            <section id="<?php echo htmlspecialchars($section['id']); ?>" class="docs-section">
                <h2><?php echo htmlspecialchars($section['title']); ?></h2>
                <?php echo $section['html']; ?>
                <?php if ($i === count($guide['sections']) - 1 && $guide['footer_html'] !== ''): ?>
                    <div class="docs-footer"><?php echo $guide['footer_html']; ?></div>
                <?php endif; ?>
            </section>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
</div>
