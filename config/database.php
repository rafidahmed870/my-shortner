<?php
require_once __DIR__ . '/config.php';

function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Ensure urls table exists
            $tableSql = "CREATE TABLE IF NOT EXISTS `urls` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `short_code` VARCHAR(20) NOT NULL,
                `original_url` TEXT NOT NULL,
                `clicks` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_short_code` (`short_code`),
                KEY `idx_short_code` (`short_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            $pdo->exec($tableSql);

        } catch (PDOException $e) {
            // Handle error securely
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
    }

    return $pdo;
}
