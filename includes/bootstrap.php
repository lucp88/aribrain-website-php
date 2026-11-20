<?php
/**
 * Loaded by index.php and by every page that touches the database or session.
 * Starts the session, enforces CSRF on POST and loads auth helpers, so pages
 * stay protected even when requested directly instead of through index.php.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';

start_secure_session();
csrf_enforce();
