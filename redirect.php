<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($code) || !preg_match('/^[a-zA-Z0-9_-]+$/', $code)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$db = getDB();

if ($db) {
    try {
        $stmt = $db->prepare("SELECT id, original_url FROM urls WHERE short_code = :code LIMIT 1");
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();

        if ($row) {
            // Increment click count
            $updateStmt = $db->prepare("UPDATE urls SET clicks = clicks + 1 WHERE id = :id");
            $updateStmt->execute([':id' => $row['id']]);

            // Perform 301 redirect
            header("Location: " . $row['original_url'], true, 301);
            exit;
        }
    } catch (Exception $e) {
        error_log("Redirect Error: " . $e->getMessage());
    }
}

// Code not found -> 404
http_response_code(404);
require __DIR__ . '/404.php';
exit;
