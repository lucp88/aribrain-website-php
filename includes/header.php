<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ARIbrain - All-Resolutions Inference for fMRI. Advanced neuroimaging analysis with True Discovery Proportion estimation.">
    <meta name="keywords" content="fMRI, neuroimaging, brain analysis, TDP, All-Resolutions Inference, neuroscience, open source">
    <meta name="author" content="Leiden University">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME . ' - ' . SITE_TAGLINE; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo">
                    <span class="logo-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 20C10 20 12 12 16 12C20 12 22 20 22 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="16" cy="10" r="2" fill="currentColor"/>
                            <circle cx="11" cy="18" r="1.5" fill="currentColor"/>
                            <circle cx="21" cy="18" r="1.5" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="logo-text">ARIbrain</span>
                </a>
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <nav class="main-nav">
                    <ul class="nav-list">
                        <?php foreach ($navItems as $item): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($item['url']); ?>" 
                                   class="<?php echo (isset($currentPage) && $currentPage === $item['page']) ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <a href="<?php echo GITHUB_URL; ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
                    </svg>
                    GitHub
                </a>
            </div>
        </div>
    </header>
    <main>
