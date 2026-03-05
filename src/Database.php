<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            self::$instance = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$instance->exec('PRAGMA journal_mode=WAL;');
            self::$instance->exec('PRAGMA foreign_keys=ON;');
            self::migrate(self::$instance);
        }
        return self::$instance;
    }

    private static function migrate(PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id        INTEGER PRIMARY KEY AUTOINCREMENT,
                username  TEXT NOT NULL UNIQUE,
                password  TEXT NOT NULL,
                theme     TEXT DEFAULT 'dark',
                created_at TEXT DEFAULT (datetime('now'))
            );

            CREATE TABLE IF NOT EXISTS students (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                username   TEXT NOT NULL UNIQUE,
                password   TEXT NOT NULL,
                full_name  TEXT NOT NULL,
                active     INTEGER NOT NULL DEFAULT 1,
                theme      TEXT DEFAULT 'dark',
                created_at TEXT DEFAULT (datetime('now'))
            );

            CREATE TABLE IF NOT EXISTS forms (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                name        TEXT NOT NULL,
                url         TEXT NOT NULL,
                max_opens   INTEGER NOT NULL DEFAULT 1,
                duration    INTEGER NOT NULL DEFAULT 30,
                active      INTEGER NOT NULL DEFAULT 1,
                created_at  TEXT DEFAULT (datetime('now'))
            );

            CREATE TABLE IF NOT EXISTS access_log (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id  INTEGER NOT NULL REFERENCES students(id),
                form_id     INTEGER NOT NULL REFERENCES forms(id),
                opened_at   TEXT DEFAULT (datetime('now'))
            );
        ");

        // Ensure theme column exists for existing installations
        try { $db->exec("ALTER TABLE admins ADD COLUMN theme TEXT DEFAULT 'dark'"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE students ADD COLUMN theme TEXT DEFAULT 'dark'"); } catch (Exception $e) {}

        // Seed default admin if table is empty
        $count = (int)$db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($count === 0) {
            $hash = password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $stmt->execute([DEFAULT_ADMIN_USER, $hash]);
        }
    }

    // ── ADMIN ─────────────────────────────────────────────────────────────────
    public static function findAdmin(string $username): array|false
    {
        $stmt = self::get()->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // ── STUDENTS ──────────────────────────────────────────────────────────────
    public static function createStudent(string $username, string $password, string $fullName): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = self::get()->prepare(
            "INSERT INTO students (username, password, full_name) VALUES (?, ?, ?)"
        );
        try {
            $stmt->execute([$username, $hash, $fullName]);
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public static function findStudent(string $username): array|false
    {
        $stmt = self::get()->prepare("SELECT * FROM students WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public static function getAllStudents(): array
    {
        return self::get()->query(
            "SELECT s.*, (SELECT COUNT(*) FROM access_log al WHERE al.student_id = s.id) AS total_opens
             FROM students s ORDER BY s.created_at DESC"
        )->fetchAll();
    }

    public static function toggleStudent(int $id): void
    {
        self::get()->prepare("UPDATE students SET active = 1 - active WHERE id = ?")->execute([$id]);
    }

    public static function deleteStudent(int $id): void
    {
        self::get()->prepare("DELETE FROM access_log WHERE student_id = ?")->execute([$id]);
        self::get()->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
    }

    // ── FORMS ─────────────────────────────────────────────────────────────────
    public static function createForm(string $name, string $url, int $maxOpens, int $duration): bool
    {
        $stmt = self::get()->prepare(
            "INSERT INTO forms (name, url, max_opens, duration) VALUES (?, ?, ?, ?)"
        );
        try {
            $stmt->execute([$name, $url, $maxOpens, $duration]);
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public static function updateForm(int $id, string $name, string $url, int $maxOpens, int $duration): void
    {
        self::get()->prepare(
            "UPDATE forms SET name=?, url=?, max_opens=?, duration=? WHERE id=?"
        )->execute([$name, $url, $maxOpens, $duration, $id]);
    }

    public static function toggleForm(int $id): void
    {
        self::get()->prepare("UPDATE forms SET active = 1 - active WHERE id = ?")->execute([$id]);
    }

    public static function deleteForm(int $id): void
    {
        self::get()->prepare("DELETE FROM access_log WHERE form_id = ?")->execute([$id]);
        self::get()->prepare("DELETE FROM forms WHERE id = ?")->execute([$id]);
    }

    public static function getAllForms(): array
    {
        return self::get()->query("SELECT * FROM forms ORDER BY created_at DESC")->fetchAll();
    }

    public static function getForm(int $id): array|false
    {
        $stmt = self::get()->prepare("SELECT * FROM forms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getActiveForms(): array
    {
        return self::get()->query("SELECT * FROM forms WHERE active = 1 ORDER BY name")->fetchAll();
    }

    // ── ACCESS LOG ────────────────────────────────────────────────────────────
    public static function countOpens(int $studentId, int $formId): int
    {
        $stmt = self::get()->prepare(
            "SELECT COUNT(*) FROM access_log WHERE student_id = ? AND form_id = ?"
        );
        $stmt->execute([$studentId, $formId]);
        return (int)$stmt->fetchColumn();
    }

    public static function logOpen(int $studentId, int $formId): void
    {
        self::get()->prepare(
            "INSERT INTO access_log (student_id, form_id) VALUES (?, ?)"
        )->execute([$studentId, $formId]);
    }

    public static function getAccessLog(): array
    {
        return self::get()->query(
            "SELECT al.opened_at, s.full_name, s.username, f.name AS form_name
             FROM access_log al
             JOIN students s ON s.id = al.student_id
             JOIN forms f ON f.id = al.form_id
             ORDER BY al.opened_at DESC
             LIMIT 200"
        )->fetchAll();
    }

    public static function getStats(): array
    {
        $db = self::get();
        return [
            'students' => (int)$db->query("SELECT COUNT(*) FROM students")->fetchColumn(),
            'forms'    => (int)$db->query("SELECT COUNT(*) FROM forms")->fetchColumn(),
            'opens'    => (int)$db->query("SELECT COUNT(*) FROM access_log")->fetchColumn(),
        ];
    }

    public static function updateTheme(string $role, int $id, string $theme): void
    {
        $table = $role === 'admin' ? 'admins' : 'students';
        $stmt = self::get()->prepare("UPDATE {$table} SET theme = ? WHERE id = ?");
        $stmt->execute([$theme, $id]);
    }
}
