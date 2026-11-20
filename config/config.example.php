<?php
define('SITE_NAME', 'ARIbrain');
define('SITE_TAGLINE', 'All-Resolutions Inference for fMRI');
define('SITE_URL', 'https://aribrain.org');
define('GITHUB_URL', 'https://github.com/AriBrain/ari-core');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'aribrain_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

define('CONTACT_EMAIL', 'contact@aribrain.org');

// Deploy pipeline (production only). Must equal the DEPLOY_TOKEN GitHub secret.
// Generate with: openssl rand -hex 32   — leave empty to disable the migration runner.
define('DEPLOY_TOKEN', '');
// Optional: where the migration runner writes database backups. Must NOT be inside the web root.
// Default: a folder "aribrain-backups" next to the web root (e.g. domains/aribrain.eu/aribrain-backups).
// define('BACKUP_DIR', '/home/youruser/domains/aribrain.eu/aribrain-backups');

$navItems = [
    ['title' => 'Home', 'url' => 'index.php', 'page' => 'home'],
    ['title' => 'ARIbrain', 'url' => 'index.php?page=product', 'page' => 'product'],
    ['title' => 'Use Cases', 'url' => 'index.php?page=use-cases', 'page' => 'use-cases'],
    ['title' => 'Installation', 'url' => 'index.php?page=install', 'page' => 'install'],
    ['title' => 'Docs', 'url' => 'index.php?page=docs', 'page' => 'docs'],
    ['title' => 'Publications', 'url' => 'index.php?page=publications', 'page' => 'publications'],
    ['title' => 'About', 'url' => 'index.php?page=about', 'page' => 'about'],
    ['title' => 'Nieuws', 'url' => 'index.php?page=blog', 'page' => 'blog'],
    ['title' => 'Forum', 'url' => 'index.php?page=forum', 'page' => 'forum'],
];
