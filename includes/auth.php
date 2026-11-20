<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Authentication for all accounts (single `users` table).
 *
 * Roles: admin (everything), editor (admin panel content), viewer (site only).
 * The session only stores the user id; the user row is reloaded on every
 * request so role changes and deactivation take effect immediately.
 */
class Auth {
    const ADMIN_PANEL_ROLES = ['admin', 'editor'];
    const INVITATION_TTL = 604800; // 7 days
    const RESET_TTL = 3600;        // 1 hour

    private $db;
    private static $currentUser = false; // false = not loaded yet

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * @param string[]|null $allowedRoles Restrict which roles may log in here.
     */
    public function login($email, $password, $allowedRoles = null) {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        $stmt = $this->db->prepare("SELECT id, role, password_hash, status FROM users WHERE email = ?");
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'Your account is not active'];
        }
        if ($allowedRoles !== null && !in_array($user['role'], $allowedRoles, true)) {
            return ['success' => false, 'error' => 'This account does not have access to the admin panel'];
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
        $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        self::$currentUser = false;

        return ['success' => true];
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        self::$currentUser = null;
    }

    /**
     * @return array|null ['id', 'name', 'email', 'role'] of the logged-in, active user.
     */
    public function currentUser() {
        if (self::$currentUser !== false) {
            return self::$currentUser;
        }
        self::$currentUser = null;

        if (empty($_SESSION['user_id']) || !$this->db) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id, name, email, role FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            // Deleted or deactivated since login.
            unset($_SESSION['user_id']);
            return null;
        }

        self::$currentUser = $user;
        return $user;
    }

    public function isLoggedIn() {
        return $this->currentUser() !== null;
    }

    public function canAccessAdminPanel() {
        $user = $this->currentUser();
        return $user !== null && in_array($user['role'], self::ADMIN_PANEL_ROLES, true);
    }

    public function isAdmin() {
        $user = $this->currentUser();
        return $user !== null && $user['role'] === 'admin';
    }

    /** For admin panel pages: admins and editors only. */
    public function requireAdminPanel() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?page=admin/login');
            exit;
        }
        if (!$this->canAccessAdminPanel()) {
            header('Location: index.php');
            exit;
        }
    }

    /** For admin-only pages (user management). */
    public function requireAdmin() {
        $this->requireAdminPanel();
        if (!$this->isAdmin()) {
            header('Location: index.php?page=admin/dashboard');
            exit;
        }
    }

    public function adminExists() {
        if (!$this->db) {
            return false;
        }
        return $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0;
    }

    /**
     * First-time setup only: refuses once any admin account exists.
     */
    public function createFirstAdmin($name, $email, $password) {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }
        if ($this->adminExists()) {
            return ['success' => false, 'error' => 'An admin account already exists'];
        }

        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'A user with this email already exists'];
        }

        try {
            $this->db->prepare("INSERT INTO users (name, email, role, password_hash, status) VALUES (?, ?, 'admin', ?, 'active')")
                ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to create admin account'];
        }
    }

    /**
     * Create a one-time token for an invitation or password reset.
     * Only the SHA-256 hash is stored; the plain token goes in the email link.
     */
    public function issueToken($userId, $ttlSeconds) {
        $token = bin2hex(random_bytes(32));
        $this->db->prepare("UPDATE users SET token_hash = ?, token_expires_at = NOW() + INTERVAL ? SECOND WHERE id = ?")
            ->execute([hash('sha256', $token), (int)$ttlSeconds, $userId]);
        return $token;
    }

    /**
     * @return array|null The invited or active user the token belongs to.
     */
    public function findUserByToken($token) {
        if (!$this->db || !is_string($token) || !preg_match('/^[0-9a-f]{64}$/', $token)) {
            return null;
        }
        $stmt = $this->db->prepare("SELECT id, name, email, role, status FROM users
            WHERE token_hash = ? AND token_expires_at > NOW() AND status IN ('invited', 'active')");
        $stmt->execute([hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Set a password using an invitation/reset token and activate the account.
     */
    public function setPasswordWithToken($token, $password) {
        $user = $this->findUserByToken($token);
        if (!$user) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE users
            SET password_hash = ?, token_hash = NULL, token_expires_at = NULL, status = 'active'
            WHERE id = ? AND token_hash = ?");
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $user['id'], hash('sha256', $token)]);
        return $stmt->rowCount() > 0;
    }
}
