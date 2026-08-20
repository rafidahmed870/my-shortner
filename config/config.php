<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

define("APP_NAME", $_ENV['APP_NAME'] ?? 'Short URL');
define("APP_URL", $_ENV['APP_URL'] ?? 'http://localhost/short-url');

// Dynamic base path & URL calculation (Works on root domain or subdirectory)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

define("BASE_PATH", $basePath);
define("DYNAMIC_APP_URL", $scheme . $host . $basePath);

define("DB_HOST", $_ENV['DB_HOST'] ?? '127.0.0.1');
define("DB_NAME", $_ENV['DB_NAME'] ?? 'short_url');
define("DB_USER", $_ENV['DB_USER'] ?? 'root');
define("DB_PASS", $_ENV['DB_PASS'] ?? '');
