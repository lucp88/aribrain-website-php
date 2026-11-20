<?php
/**
 * Deploy-time migration runner.
 *
 * NOT part of the normal site: the GitHub "Deploy" workflow uploads this file
 * (plus sql/migrations/*.sql) to <site>/_deploy/ at the start of a deploy and
 * deletes it again at the end. It is excluded from the regular code sync.
 *
 * Every request must be a POST with header "X-Deploy-Token" equal to
 * DEPLOY_TOKEN from config/config.php. Without a configured token it refuses
 * everything.
 *
 * Actions (POST field "action"):
 *   status           List applied and pending migrations (read-only)
 *   migrate          Maintenance mode on, back up the database, run pending migrations
 *   maintenance_off  Remove the maintenance flag
 *
 * Responses are JSON and never contain database contents or server paths:
 * workflow logs of a public repository are public. Full error details go to a
 * log file in the backup directory.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$siteRoot = dirname(__DIR__);
$maintenanceFlag = $siteRoot . '/.maintenance';

function respond($code, array $body) {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    exit;
}

// ---------------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'POST required']);
}

require $siteRoot . '/config/config.php';

if (!defined('DEPLOY_TOKEN') || !is_string(DEPLOY_TOKEN) || strlen(DEPLOY_TOKEN) < 32) {
    respond(503, ['error' => 'DEPLOY_TOKEN is not configured in config/config.php on the server']);
}
$sentToken = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '';
if (!is_string($sentToken) || !hash_equals(DEPLOY_TOKEN, $sentToken)) {
    respond(403, ['error' => 'Invalid deploy token']);
}

// ---------------------------------------------------------------------------
// Setup
// ---------------------------------------------------------------------------
@set_time_limit(300);

$migrationsDir = is_dir(__DIR__ . '/migrations') ? __DIR__ . '/migrations' : $siteRoot . '/sql/migrations';
// Default: next to the web root (e.g. domains/aribrain.eu/aribrain-backups), so never downloadable.
$backupDir = defined('BACKUP_DIR') ? rtrim(BACKUP_DIR, '/') : dirname($siteRoot) . '/aribrain-backups';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true, // text protocol, needed for PREPARE/EXECUTE in migrations
        ]
    );
} catch (PDOException $e) {
    respond(500, ['error' => 'Database connection failed']);
}

function available_migrations($dir) {
    $names = [];
    foreach (glob($dir . '/*.sql') ?: [] as $file) {
        $name = basename($file, '.sql');
        if (preg_match('/^\d{3}_[a-z0-9_]+$/', $name)) {
            $names[$name] = $file;
        }
    }
    ksort($names, SORT_STRING);
    return $names;
}

function applied_migrations(PDO $pdo) {
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'schema_migrations'")->fetchColumn();
    if (!$exists) {
        return [];
    }
    return $pdo->query("SELECT migration FROM schema_migrations ORDER BY migration")->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Split a migration file into statements. Handles quotes, backticks and
 * comments. DELIMITER (stored procedures/triggers) is not supported.
 */
function split_sql($sql) {
    $statements = [];
    $buf = '';
    $len = strlen($sql);
    $quote = null;

    for ($i = 0; $i < $len; $i++) {
        $c = $sql[$i];
        $next = $i + 1 < $len ? $sql[$i + 1] : '';

        if ($quote !== null) {
            $buf .= $c;
            if ($c === '\\' && $quote !== '`') {
                $buf .= $next;
                $i++;
            } elseif ($c === $quote) {
                if ($next === $quote) { // doubled quote = escaped quote
                    $buf .= $next;
                    $i++;
                } else {
                    $quote = null;
                }
            }
            continue;
        }

        if ($c === "'" || $c === '"' || $c === '`') {
            $quote = $c;
            $buf .= $c;
        } elseif ($c === '#' || ($c === '-' && $next === '-' && ($i + 2 >= $len || ctype_space($sql[$i + 2])))) {
            $end = strpos($sql, "\n", $i);
            $i = $end === false ? $len : $end;
            $buf .= "\n";
        } elseif ($c === '/' && $next === '*') {
            $end = strpos($sql, '*/', $i + 2);
            $i = $end === false ? $len : $end + 1;
            $buf .= ' ';
        } elseif ($c === ';') {
            if (trim($buf) !== '') {
                $statements[] = trim($buf);
            }
            $buf = '';
        } else {
            $buf .= $c;
        }
    }
    if (trim($buf) !== '') {
        $statements[] = trim($buf);
    }
    return $statements;
}

/**
 * Full SQL backup (structure + data), importable via phpMyAdmin.
 * Returns the backup file name.
 */
function backup_database(PDO $pdo, $dir, $label) {
    $gzip = function_exists('gzopen');
    $name = 'backup-' . date('Ymd-His') . '-before-' . $label . '.sql' . ($gzip ? '.gz' : '');
    $path = $dir . '/' . $name;

    $h = $gzip ? gzopen($path, 'wb6') : fopen($path, 'wb');
    if (!$h) {
        throw new RuntimeException('Could not open backup file for writing');
    }
    $write = function ($s) use ($h, $gzip) {
        $ok = $gzip ? gzwrite($h, $s) : fwrite($h, $s);
        if ($ok === false) {
            throw new RuntimeException('Could not write backup file');
        }
    };

    $write("-- ARIbrain database backup, " . date('c') . "\n"
        . "-- Restore: phpMyAdmin -> Import this file\n\n"
        . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

    $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
    foreach ($tables as $row) {
        $table = $row[0];
        $create = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_NUM)[1];
        $write("DROP TABLE IF EXISTS `$table`;\n$create;\n\n");

        $batch = [];
        $flush = function () use (&$batch, $write, $table) {
            if ($batch) {
                $write("INSERT INTO `$table` VALUES\n" . implode(",\n", $batch) . ";\n");
                $batch = [];
            }
        };
        $rows = $pdo->query('SELECT * FROM `' . str_replace('`', '``', $table) . '`', PDO::FETCH_NUM);
        foreach ($rows as $r) {
            $values = array_map(function ($v) use ($pdo) {
                return $v === null ? 'NULL' : $pdo->quote((string)$v);
            }, $r);
            $batch[] = '(' . implode(',', $values) . ')';
            if (count($batch) >= 200) {
                $flush();
            }
        }
        $flush();
        $write("\n");
    }

    $write("SET FOREIGN_KEY_CHECKS=1;\n");
    $gzip ? gzclose($h) : fclose($h);

    if (!filesize($path)) {
        throw new RuntimeException('Backup file is empty');
    }
    return $name;
}

function log_error($dir, $message) {
    $file = 'migrate-error-' . date('Ymd-His') . '.log';
    @file_put_contents($dir . '/' . $file, $message . "\n", FILE_APPEND);
    return $file;
}

// ---------------------------------------------------------------------------
// Actions
// ---------------------------------------------------------------------------
$action = $_POST['action'] ?? '';
$available = available_migrations($migrationsDir);

if ($action === 'status') {
    $applied = applied_migrations($pdo);
    respond(200, [
        'applied' => $applied,
        'pending' => array_values(array_diff(array_keys($available), $applied)),
        'maintenance' => file_exists($maintenanceFlag),
    ]);
}

if ($action === 'maintenance_off') {
    if (file_exists($maintenanceFlag) && !@unlink($maintenanceFlag)) {
        respond(500, ['error' => 'Could not remove the maintenance flag; delete .maintenance via FTP']);
    }
    respond(200, ['maintenance' => false]);
}

if ($action === 'migrate') {
    if (!$pdo->query("SELECT GET_LOCK('aribrain_migrate', 0)")->fetchColumn()) {
        respond(409, ['error' => 'Another migration is already running']);
    }

    $pending = array_values(array_diff(array_keys($available), applied_migrations($pdo)));

    if (!$pending) {
        respond(200, ['applied' => [], 'backup' => null, 'maintenance' => file_exists($maintenanceFlag)]);
    }

    // No backup, no migration.
    if (!is_dir($backupDir) && !@mkdir($backupDir, 0700, true)) {
        respond(500, ['error' => 'Backup directory does not exist and could not be created (see BACKUP_DIR)']);
    }
    if (!is_writable($backupDir)) {
        respond(500, ['error' => 'Backup directory is not writable (see BACKUP_DIR)']);
    }

    if (!@touch($maintenanceFlag)) {
        respond(500, ['error' => 'Could not enable maintenance mode']);
    }

    try {
        $backup = backup_database($pdo, $backupDir, $pending[0]);
    } catch (Throwable $e) {
        @unlink($maintenanceFlag); // nothing changed yet, site can stay up
        $log = log_error($backupDir, 'Backup failed: ' . $e->getMessage());
        respond(500, ['error' => 'Backup failed, no migrations were run', 'log' => $log, 'maintenance' => false]);
    }

    // Created after the backup, so the backup is an exact pre-migration snapshot.
    $pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
        migration VARCHAR(255) NOT NULL PRIMARY KEY,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $done = [];
    foreach ($pending as $name) {
        $statements = split_sql(file_get_contents($available[$name]));
        foreach ($statements as $index => $sql) {
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                $log = log_error($backupDir, "Migration $name failed at statement " . ($index + 1)
                    . "\nError: " . $e->getMessage() . "\nStatement:\n$sql");
                respond(500, [
                    'error' => 'Migration failed',
                    'failed_migration' => $name,
                    'failed_statement' => $index + 1,
                    'sqlstate' => $e->errorInfo[0] ?? null,
                    'mysql_errno' => $e->errorInfo[1] ?? null,
                    'applied' => $done,
                    'backup' => $backup,
                    'log' => $log,
                    'maintenance' => true,
                ]);
            }
        }
        $pdo->prepare("INSERT INTO schema_migrations (migration) VALUES (?)")->execute([$name]);
        $done[] = $name;
    }

    respond(200, ['applied' => $done, 'backup' => $backup, 'maintenance' => true]);
}

respond(400, ['error' => 'Unknown action']);
