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
            CREATE TABLE IF NOT EXISTS forms (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                name        TEXT NOT NULL,
                url         TEXT NOT NULL,
                max_opens   INTEGER NOT NULL DEFAULT 1,
                duration    INTEGER NOT NULL DEFAULT 30,
                active      INTEGER NOT NULL DEFAULT 1,
                created_at  TEXT DEFAULT (datetime('now'))
            );

            CREATE TABLE IF NOT EXISTS settings (
                key         TEXT PRIMARY KEY,
                value       TEXT NOT NULL
            );
        ");

        // Seed default settings
        $count = (int)$db->query("SELECT COUNT(*) FROM settings WHERE key = 'footer_height'")->fetchColumn();
        if ($count === 0) {
            $db->exec("INSERT INTO settings (key, value) VALUES ('footer_height', '155')");
        }
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

    public static function getStats(): array
    {
        $db = self::get();
        return [
            'forms'    => (int)$db->query("SELECT COUNT(*) FROM forms")->fetchColumn(),
        ];
    }

    public static function getSetting(string $key, $default = null): mixed
    {
        $stmt = self::get()->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $default;
    }

    public static function setSetting(string $key, $value): void
    {
        $stmt = self::get()->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
        $stmt->execute([$key, (string)$value]);
    }
}
