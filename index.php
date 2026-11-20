<?php
require_once __DIR__ . '/includes/bootstrap.php';

$page = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : 'home';
$auth = new Auth();

if ($page === 'logout') {
    $auth->logout();
    header('Location: index.php');
    exit;
}

// Pages with their own layout, reachable without passing the gate.
// Admin pages do their own access checks.
$standalonePages = [
    'admin/login', 'admin/dashboard', 'admin/publications', 'admin/forum',
    'admin/users', 'admin/news', 'admin/pages',
    'user/login', 'set-password', 'forgot-password',
];
if (in_array($page, $standalonePages, true)) {
    require __DIR__ . '/pages/' . $page . '.php';
    exit;
}

// Gate: require login for public pages while the site is under construction.
// Remove this block to make the site public.
if (!$auth->isLoggedIn()) {
    require __DIR__ . '/pages/gate.php';
    exit;
}

$validPages = ['home', 'product', 'install', 'docs', 'use-cases', 'publications', 'about', 'blog', 'forum'];
if (!in_array($page, $validPages, true)) {
    $page = 'home';
}

$currentPage = $page;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/pages/' . $page . '.php';
require_once __DIR__ . '/includes/footer.php';
