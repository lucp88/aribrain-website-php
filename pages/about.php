<?php
$pageTitle = 'About Us';
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$teamMembers = $db->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll();
?>

<div class="page-header">
    <div class="container">
        <h1>About Us</h1>
        <p>Meet the team behind ARIbrain</p>
    </div>
</div>

<?php if (empty($teamMembers)): ?>
<div class="container">
    <div class="placeholder-content">
        <div class="icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <h2>Content Coming Soon</h2>
        <p>Information about our mission, team members at Leiden University, and how to get in touch is being prepared.</p>
        <a href="https://www.universiteitleiden.nl" class="btn btn-primary" target="_blank" rel="noopener">Visit Leiden University</a>
    </div>
</div>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="team-grid">
            <?php foreach ($teamMembers as $member): ?>
            <div class="team-card">
                <div class="team-avatar">
                    <?php if (!empty($member['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($member['image_url']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" style="object-position: <?php echo intval($member['image_pos_x'] ?? 50); ?>% <?php echo intval($member['image_pos_y'] ?? 50); ?>%;">
                    <?php else: ?>
                        <div class="team-avatar-placeholder">
                            <?php echo strtoupper(mb_substr($member['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="team-info">
                    <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p class="team-role"><?php echo htmlspecialchars($member['role']); ?></p>
                    <?php if ($member['bio']): ?>
                        <p class="team-bio"><?php echo htmlspecialchars($member['bio']); ?></p>
                    <?php endif; ?>
                    <div class="team-links">
                        <?php if ($member['email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" title="Email">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($member['website_url']): ?>
                            <a href="<?php echo htmlspecialchars($member['website_url']); ?>" target="_blank" rel="noopener" title="Website">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
