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
            $originalUrl = $row['original_url'];

            // Increment click count
            $updateStmt = $db->prepare("UPDATE urls SET clicks = clicks + 1 WHERE id = :id");
            $updateStmt->execute([':id' => $row['id']]);

            $shortUrl = DYNAMIC_APP_URL . '/' . rawurlencode($code);
            $siteTitle = APP_NAME;
            $siteDescription = "Shorten long links instantly with private browser history.";
?>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?></title>

                <!-- Canonical Tag -->
                <link rel="canonical" href="<?php echo htmlspecialchars($shortUrl, ENT_QUOTES, 'UTF-8'); ?>">

                <!-- Open Graph Metadata (Default Site Branding) -->
                <meta property="og:type" content="website">
                <meta property="og:site_name" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
                <meta property="og:url" content="<?php echo htmlspecialchars($shortUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <meta property="og:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
                <meta property="og:description" content="<?php echo htmlspecialchars($siteDescription, ENT_QUOTES, 'UTF-8'); ?>">

                <!-- Twitter Card Metadata -->
                <meta name="twitter:card" content="summary">
                <meta name="twitter:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
                <meta name="twitter:description" content="<?php echo htmlspecialchars($siteDescription, ENT_QUOTES, 'UTF-8'); ?>">

                <!-- Local Font & Stylesheets -->
                <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/poppins.css">
                <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/redirect.css">

                <!-- Pure JS Redirect Only -->
                <script>
                    (function() {
                        var target = <?php echo json_encode($originalUrl); ?>;
                        if (target) {
                            window.location.replace(target);
                        }
                    })();
                </script>
            </head>

            <body>

                <div class="redirect-card">
                    <div class="loader-wrapper"></div>
                    <h1>Redirecting...</h1>
                    <p>Please wait while we redirect you to your destination.</p>

                    <a href="<?php echo htmlspecialchars($originalUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn-redirect">
                        Click here if you are not redirected automatically
                    </a>
                </div>

            </body>

            </html>
<?php
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