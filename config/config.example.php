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
