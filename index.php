<?php
require_once __DIR__ . '/config/config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$adminPages = ['admin/login', 'admin/dashboard', 'admin/publications', 'admin/forum', 'admin/users', 'admin/news', 'admin/pages'];
$userPages = ['user/login', 'set-password'];
if (in_array($page, $adminPages) || in_array($page, $userPages)) {
    $pageFile = __DIR__ . '/pages/' . $page . '.php';
    if (file_exists($pageFile)) {
        require_once $pageFile;
    } else {
        header('Location: index.php');
        exit;
    }
    exit;
}

// Gate: require user login for public pages while site is under construction
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$isUser = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
if (!$isAdmin && !$isUser) {
    require_once __DIR__ . '/pages/gate.php';
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
