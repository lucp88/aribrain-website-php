<?php
require_once __DIR__ . '/../../includes/admin/auth.php';

$auth = new AdminAuth();
$auth->requireLogin();

$currentAdmin = $auth->getCurrentAdmin();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
    header('Location: index.php?page=admin/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php $activePage = 'dashboard'; require __DIR__ . '/../../includes/admin/sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($currentAdmin['username']); ?></span>
                </div>
            </header>
            
            <div class="admin-content">
                <div class="admin-cards">
                    <a href="index.php?page=admin/publications" class="admin-card">
                        <div class="admin-card-icon publications">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <div class="admin-card-content">
                            <h3>Publications</h3>
                            <p>Manage scientific publications, papers, and talks</p>
                            <span class="admin-card-status admin-card-status-active">Active</span>
                        </div>
                    </a>
                    
                    <a href="index.php?page=admin/news" class="admin-card">
                        <div class="admin-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); color: white;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path>
                                <path d="M18 14h-8"></path>
                                <path d="M15 18h-5"></path>
                                <path d="M10 6h8v4h-8V6z"></path>
                            </svg>
                        </div>
                        <div class="admin-card-content">
                            <h3>News</h3>
                            <p>Publish news posts and updates</p>
                            <span class="admin-card-status admin-card-status-active">Active</span>
                        </div>
                    </a>

                    <a href="index.php?page=admin/pages" class="admin-card">
                        <div class="admin-card-icon" style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); color: white;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </div>
                        <div class="admin-card-content">
                            <h3>Pages</h3>
                            <p>Edit ARIbrain, Use Cases, and Team pages</p>
                            <span class="admin-card-status admin-card-status-active">Active</span>
                        </div>
                    </a>

                    <a href="index.php?page=admin/forum" class="admin-card">
                        <div class="admin-card-icon forum">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                        <div class="admin-card-content">
                            <h3>Forum</h3>
                            <p>Moderate forum posts and manage questions</p>
                            <span class="admin-card-status admin-card-status-active">Active</span>
                        </div>
                    </a>

                    <?php if ($currentAdmin['role'] === 'admin'): ?>
                    <a href="index.php?page=admin/users" class="admin-card">
                        <div class="admin-card-icon users">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="admin-card-content">
                            <h3>Users</h3>
                            <p>Invite and manage user accounts</p>
                            <span class="admin-card-status admin-card-status-active">Active</span>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
                
                <div class="admin-info-box">
                    <h3>Getting Started</h3>
                    <p>Welcome to the ARIbrain admin panel. The management sections for Publications and Forum are currently under development. Once activated, you'll be able to:</p>
                    <ul>
                        <li>Add and edit scientific publications that will appear on the website</li>
                        <li>Moderate forum posts and manage discussion categories</li>
                        <li>Manage admin user accounts</li>
                        <li>Configure website settings</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
