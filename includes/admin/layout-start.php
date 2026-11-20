<?php
// Shared admin panel layout.
// Expects: $adminTitle (h1 + browser title), $activePage (sidebar key), $currentUser
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($adminTitle); ?> - Admin - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php require __DIR__ . '/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1><?php echo htmlspecialchars($adminTitle); ?></h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?></span>
                </div>
            </header>

            <div class="admin-content">
