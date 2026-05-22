<?php

/**
 * Aplica migraciones del módulo comercial (MySQL o SQLite).
 * Uso: php scripts/run-sales-migrations.php
 */

function envValue(string $key, string $default = ''): string
{
    $envPath = __DIR__ . '/../.env';
    if (! is_file($envPath)) {
        return $default;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        if (trim($k) === $key) {
            return trim($v, " \t\"'");
        }
    }

    return $default;
}

$driver = envValue('DB_CONNECTION', 'mysql');
$isSqlite = $driver === 'sqlite';

if ($isSqlite) {
    $dbPath = envValue('DB_DATABASE', 'database/database.sqlite');
    if ($dbPath !== '' && $dbPath[0] !== '/' && strpos($dbPath, ':') === false) {
        $dbPath = __DIR__ . '/../' . $dbPath;
    }
    if (! is_file($dbPath)) {
        fwrite(STDERR, "SQLite no encontrado: {$dbPath}\n");
        exit(1);
    }
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        fwrite(STDERR, "Habilita extension=pdo_sqlite en php.ini del CLI o ejecuta: php artisan migrate\n");
        exit(1);
    }
    $pdo = new PDO('sqlite:' . $dbPath);
} else {
    if (! in_array('mysql', PDO::getAvailableDrivers(), true)) {
        fwrite(STDERR, "Habilita extension=pdo_mysql en php.ini del CLI.\n");
        exit(1);
    }
    $host = envValue('DB_HOST', '127.0.0.1');
    $port = envValue('DB_PORT', '3306');
    $db = envValue('DB_DATABASE', 'laravel');
    $user = envValue('DB_USERNAME', 'root');
    $pass = envValue('DB_PASSWORD', '');
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($isSqlite) {
    $pdo->exec('PRAGMA foreign_keys = ON');
}

function columnExists(PDO $pdo, string $table, string $column, bool $isSqlite): bool
{
    if ($isSqlite) {
        $stmt = $pdo->query("PRAGMA table_info({$table})");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (($row['name'] ?? '') === $column) {
                return true;
            }
        }

        return false;
    }
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);

    return (int) $stmt->fetchColumn() > 0;
}

function tableExists(PDO $pdo, string $table, bool $isSqlite): bool
{
    if ($isSqlite) {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
        $stmt->execute([$table]);

        return (bool) $stmt->fetchColumn();
    }
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);

    return (int) $stmt->fetchColumn() > 0;
}

function recordMigration(PDO $pdo, string $name, bool $isSqlite): void
{
    if (! tableExists($pdo, 'migrations', $isSqlite)) {
        return;
    }
    $exists = $pdo->prepare('SELECT 1 FROM migrations WHERE migration = ?');
    $exists->execute([$name]);
    if ($exists->fetchColumn()) {
        echo "Ya registrada: {$name}\n";

        return;
    }
    $batch = (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations')->fetchColumn();
    $insert = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
    $insert->execute([$name, $batch]);
    echo "Registrada: {$name}\n";
}

echo "Conexión: {$driver}\n";

if (! columnExists($pdo, 'users', 'trial_plan_key', $isSqlite)) {
    $pdo->exec('ALTER TABLE users ADD trial_plan_key VARCHAR(32) NULL');
    echo "users.trial_plan_key OK\n";
    recordMigration($pdo, '2026_05_23_100000_add_trial_plan_key_to_users_table', $isSqlite);
}

if (! columnExists($pdo, 'users', 'tvpik_extra_screens', $isSqlite)) {
    $pdo->exec('ALTER TABLE users ADD tvpik_extra_screens INTEGER NOT NULL DEFAULT 0');
    echo "users.tvpik_extra_screens OK\n";
    recordMigration($pdo, '2026_05_24_100000_add_tvpik_extra_screens_to_users_table', $isSqlite);
}

if (! columnExists($pdo, 'companies', 'sales_rep_user_id', $isSqlite)) {
    $pdo->exec('ALTER TABLE companies ADD sales_rep_user_id BIGINT UNSIGNED NULL');
    $pdo->exec('ALTER TABLE companies ADD sales_converted_at TIMESTAMP NULL');
    try {
        $pdo->exec('ALTER TABLE companies ADD INDEX companies_sales_rep_user_id_index (sales_rep_user_id)');
    } catch (PDOException $e) {
        /* índice ya existe */
    }
    echo "companies.sales_rep_* OK\n";
    recordMigration($pdo, '2026_05_23_100100_add_sales_fields_to_companies_table', $isSqlite);
}

if (! tableExists($pdo, 'sales_handoffs', $isSqlite)) {
    if ($isSqlite) {
        $pdo->exec(<<<'SQL'
CREATE TABLE sales_handoffs (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    sales_rep_user_id INTEGER NOT NULL,
    company_id INTEGER NOT NULL,
    prospect_email VARCHAR(255) NOT NULL,
    prospect_name VARCHAR(255) NULL,
    plan_key VARCHAR(32) NOT NULL,
    trial_days INTEGER NOT NULL DEFAULT 30,
    restaurant_user_id INTEGER NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'sent',
    sent_at DATETIME NULL,
    converted_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (sales_rep_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_user_id) REFERENCES users(id) ON DELETE SET NULL
)
SQL);
    } else {
        $pdo->exec(<<<'SQL'
CREATE TABLE sales_handoffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sales_rep_user_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    prospect_email VARCHAR(255) NOT NULL,
    prospect_name VARCHAR(255) NULL,
    plan_key VARCHAR(32) NOT NULL,
    trial_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    restaurant_user_id BIGINT UNSIGNED NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'sent',
    sent_at TIMESTAMP NULL,
    converted_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX sales_handoffs_sales_rep_user_id_index (sales_rep_user_id),
    INDEX sales_handoffs_sent_at_index (sent_at),
    INDEX sales_handoffs_prospect_email_index (prospect_email),
    INDEX sales_handoffs_status_index (status),
    CONSTRAINT sales_handoffs_sales_rep_user_id_foreign FOREIGN KEY (sales_rep_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT sales_handoffs_company_id_foreign FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT sales_handoffs_restaurant_user_id_foreign FOREIGN KEY (restaurant_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }
    echo "sales_handoffs OK\n";
    recordMigration($pdo, '2026_05_23_100200_create_sales_handoffs_table', $isSqlite);
}

if (! columnExists($pdo, 'products', 'sales_demo_highlight', $isSqlite)) {
    if ($isSqlite) {
        $pdo->exec('ALTER TABLE products ADD sales_demo_highlight TINYINT(1) NOT NULL DEFAULT 0');
    } else {
        $pdo->exec('ALTER TABLE products ADD sales_demo_highlight TINYINT(1) NOT NULL DEFAULT 0 AFTER highlight');
    }
    echo "products.sales_demo_highlight OK\n";
    recordMigration($pdo, '2026_05_23_100300_add_sales_demo_highlight_to_products_table', $isSqlite);
}

echo "\nListo. Recarga /admin/platform/comercial\n";
