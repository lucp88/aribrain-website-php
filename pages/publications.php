<?php 
$pageTitle = 'Publications & Talks';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();
$talks = [];
$papers = [];

if ($db) {
    $talks = $db->query("SELECT * FROM publications WHERE pub_type = 'talk' ORDER BY is_featured DESC, pub_date DESC, year DESC")->fetchAll();
    $papers = $db->query("SELECT * FROM publications WHERE pub_type = 'paper' ORDER BY is_featured DESC, year DESC")->fetchAll();
}

function formatAuthors($authors) {
    return preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', htmlspecialchars($authors));
}

function renderTopics($topics) {
    if (empty($topics)) return '';
    $tags = array_map('trim', explode(',', $topics));
    $html = '<div class="publication-topics">';
    foreach ($tags as $tag) {
        $html .= '<span class="topic-tag">' . htmlspecialchars($tag) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

function getVideoEmbed($url) {
    if (empty($url)) return '';
    
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $videoId = $matches[1];
        return '<div class="publication-video"><iframe src="https://www.youtube.com/embed/' . htmlspecialchars($videoId) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
    }
    
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        $videoId = $matches[1];
        return '<div class="publication-video"><iframe src="https://player.vimeo.com/video/' . htmlspecialchars($videoId) . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
    }
    
    return '';
}
?>

<div class="page-header">
    <div class="container">
        <h1>Publications & Talks</h1>
        <p>Scientific communications, conference presentations, and peer-reviewed publications</p>
    </div>
</div>

<?php if (!empty($talks)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Conference Presentations</h2>
            <p>Recent talks and poster presentations at scientific conferences</p>
        </div>

        <?php foreach ($talks as $talk): ?>
        <div class="publication-card <?php echo $talk['is_featured'] ? 'featured' : ''; ?>">
            <?php if ($talk['conference']): ?>
                <div class="publication-badge"><?php echo htmlspecialchars($talk['conference']); ?></div>
            <?php endif; ?>
            <div class="publication-content">
                <div class="publication-meta">
                    <?php if ($talk['presentation_type']): ?>
                    <span class="publication-type">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        <?php echo htmlspecialchars($talk['presentation_type']); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($talk['pub_date']): ?>
                    <span class="publication-date">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <?php echo date('F j, Y', strtotime($talk['pub_date'])); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($talk['location']): ?>
                    <span class="publication-location">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?php echo htmlspecialchars($talk['location']); ?>
                    </span>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($talk['title']); ?></h3>
                <p class="publication-authors"><?php echo formatAuthors($talk['authors']); ?></p>
                <?php if ($talk['description']): ?>
                <p class="publication-description"><?php echo htmlspecialchars($talk['description']); ?></p>
                <?php endif; ?>
                <?php echo renderTopics($talk['topics']); ?>
                <?php echo getVideoEmbed($talk['video_url'] ?? ''); ?>
                <?php if ($talk['url'] || $talk['github_url']): ?>
                <div class="publication-links">
                    <?php if ($talk['url']): ?>
                    <a href="<?php echo htmlspecialchars($talk['url']); ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        View Materials
                    </a>
                    <?php endif; ?>
                    <?php if ($talk['github_url']): ?>
                    <a href="<?php echo htmlspecialchars($talk['github_url']); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
                        </svg>
                        GitHub
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($papers)): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Key Publications</h2>
            <p>Foundational papers on the All-Resolutions Inference framework</p>
        </div>
        
        <?php foreach ($papers as $paper): ?>
        <div class="publication-card paper">
            <div class="publication-content">
                <div class="publication-meta">
                    <span class="publication-type">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Journal Article
                    </span>
                    <span class="publication-date">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <?php echo $paper['year']; ?>
                    </span>
                </div>
                <h3><?php echo htmlspecialchars($paper['title']); ?></h3>
                <p class="publication-authors"><?php echo formatAuthors($paper['authors']); ?></p>
                <?php if ($paper['journal']): ?>
                <p class="publication-journal"><em><?php echo htmlspecialchars($paper['journal']); ?></em></p>
                <?php endif; ?>
                <?php if ($paper['description']): ?>
                <p class="publication-description"><?php echo htmlspecialchars($paper['description']); ?></p>
                <?php endif; ?>
                <?php echo renderTopics($paper['topics']); ?>
                <?php echo getVideoEmbed($paper['video_url'] ?? ''); ?>
                <?php if ($paper['doi'] || $paper['url']): ?>
                <div class="publication-links">
                    <?php if ($paper['doi']): ?>
                    <a href="https://doi.org/<?php echo htmlspecialchars($paper['doi']); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        View Paper
                    </a>
                    <?php elseif ($paper['url']): ?>
                    <a href="<?php echo htmlspecialchars($paper['url']); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        View Paper
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (empty($talks) && empty($papers)): ?>
<section class="section">
    <div class="container">
        <div class="placeholder-content">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <h2>No Publications Yet</h2>
            <p>Check back soon for scientific publications and conference presentations.</p>
        </div>
    </div>
</section>
<?php endif; ?>
