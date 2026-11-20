<?php
require_once __DIR__ . '/../../config/database.php';

class AdminAuth {
    private $db;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($username, $password) {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Check admins table first (always full admin)
        $stmt = $this->db->prepare("SELECT id, username, password_hash, is_active FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin) {
            if (!$admin['is_active']) {
                return ['success' => false, 'error' => 'Account is disabled'];
            }
            if (password_verify($password, $admin['password_hash'])) {
                $this->db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_role'] = 'admin';
                return ['success' => true];
            }
        }

        // Check users table for admin or editor role users
        $stmt = $this->db->prepare("SELECT id, name, email, role, password_hash, status FROM users WHERE email = ? AND role IN ('admin', 'editor')");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['status'] !== 'active') {
                return ['success' => false, 'error' => 'Account is not active'];
            }
            if (password_verify($password, $user['password_hash'])) {
                $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['name'];
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_role'] = $user['role'];
                return ['success' => true];
            }
        }

        return ['success' => false, 'error' => 'Invalid credentials'];
    }
    
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?page=admin/login');
            exit;
        }
    }
    
    public function createAdmin($username, $email, $password) {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }
        
        $stmt = $this->db->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Username or email already exists'];
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $passwordHash]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to create admin account'];
        }
    }
    
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'role' => $_SESSION['admin_role'] ?? 'admin'
        ];
    }

    public function isAdmin() {
        return $this->isLoggedIn() && ($_SESSION['admin_role'] ?? 'admin') === 'admin';
    }

    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: index.php?page=admin/dashboard');
            exit;
        }
    }
    
    public function adminExists() {
        if (!$this->db) {
            return false;
        }
        $stmt = $this->db->query("SELECT COUNT(*) FROM admins");
        return $stmt->fetchColumn() > 0;
    }
}
