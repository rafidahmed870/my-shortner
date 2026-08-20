CREATE TABLE IF NOT EXISTS `urls` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `short_code` VARCHAR(20) NOT NULL,
    `original_url` TEXT NOT NULL,
    `clicks` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_short_code` (`short_code`),
    KEY `idx_short_code` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
