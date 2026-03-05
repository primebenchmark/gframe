<?php
declare(strict_types=1);

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    public static function loginAdmin(string $username, string $password): bool
    {
        $admin = Database::findAdmin($username);
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            $_SESSION['role']       = 'admin';
            $_SESSION['theme']      = $admin['theme'] ?? 'dark';
            return true;
        }
        return false;
    }

    public static function loginStudent(string $username, string $password): bool
    {
        $student = Database::findStudent($username);
        if ($student && $student['active'] && password_verify($password, $student['password'])) {
            $_SESSION['student_id']   = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['student_user'] = $student['username'];
            $_SESSION['role']         = 'student';
            $_SESSION['theme']        = $student['theme'] ?? 'dark';
            return true;
        }
        return false;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    public static function isStudent(): bool
    {
        return ($_SESSION['role'] ?? '') === 'student';
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header('Location: /login.php?role=admin');
            exit;
        }
    }

    public static function requireStudent(): void
    {
        if (!self::isStudent()) {
            header('Location: /login.php?role=student');
            exit;
        }
    }

    public static function logout(): void
    {
        $theme = $_SESSION['theme'] ?? $_COOKIE['theme'] ?? 'dark';
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Persist theme in cookie after logout
        setcookie('theme', $theme, [
            'expires' => time() + (86400 * 365),
            'path' => '/',
            'samesite' => 'Lax'
        ]);

        header('Location: /');
        exit;
    }
}
