<?php
// Usage: $activePage = 'dashboard' | 'publications' | 'news' | 'forum' | 'pages' | 'users'
// Requires $currentAdmin from auth
$sidebarActivePage = $activePage ?? '';
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <a href="index.php" class="logo">
            <img src="assets/images/aribrain-logo.svg" alt="ARIbrain" width="32" height="32">
            <span><?php echo SITE_NAME; ?></span>
        </a>
    </div>

    <nav class="admin-nav">
        <a href="index.php?page=admin/dashboard" class="admin-nav-item <?php echo $sidebarActivePage === 'dashboard' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>
        <a href="index.php?page=admin/publications" class="admin-nav-item <?php echo $sidebarActivePage === 'publications' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            Publications
        </a>
        <a href="index.php?page=admin/news" class="admin-nav-item <?php echo $sidebarActivePage === 'news' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path>
                <path d="M18 14h-8"></path>
                <path d="M15 18h-5"></path>
                <path d="M10 6h8v4h-8V6z"></path>
            </svg>
            News
        </a>
        <a href="index.php?page=admin/pages" class="admin-nav-item <?php echo $sidebarActivePage === 'pages' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Pages
        </a>
        <a href="index.php?page=admin/forum" class="admin-nav-item <?php echo $sidebarActivePage === 'forum' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            Forum
        </a>
        <?php if (($currentAdmin['role'] ?? 'admin') === 'admin'): ?>
        <a href="index.php?page=admin/users" class="admin-nav-item <?php echo $sidebarActivePage === 'users' ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Users
        </a>
        <?php endif; ?>
    </nav>

    <div class="admin-sidebar-footer">
        <a href="index.php?page=admin/dashboard&action=logout" class="admin-nav-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Logout
        </a>
    </div>
</aside>
