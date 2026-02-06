<?php
require_once __DIR__ . '/config/config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$adminPages = ['admin/login', 'admin/dashboard', 'admin/publications', 'admin/forum'];
if (in_array($page, $adminPages)) {
    $pageFile = __DIR__ . '/pages/' . $page . '.php';
    if (file_exists($pageFile)) {
        require_once $pageFile;
    } else {
        header('Location: index.php');
        exit;
    }
    exit;
}

$validPages = ['home', 'product', 'install', 'docs', 'use-cases', 'publications', 'about', 'blog', 'forum'];
if (!in_array($page, $validPages)) {
    $page = 'home';
}

$currentPage = $page;

require_once __DIR__ . '/includes/header.php';

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    require_once __DIR__ . '/pages/home.php';
}

require_once __DIR__ . '/includes/footer.php';
