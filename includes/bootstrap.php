<?php
/**
 * Loaded by index.php and by every page that touches the database or session.
 * Starts the session, enforces CSRF on POST and loads auth helpers, so pages
 * stay protected even when requested directly instead of through index.php.
 */

// Maintenance mode while a deploy migrates the database (see deploy/migrate.php).
if (file_exists(dirname(__DIR__) . '/.maintenance')) {
    require __DIR__ . '/maintenance.php';
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';

start_secure_session();
csrf_enforce();
