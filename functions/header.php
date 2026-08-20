<?php if (!defined('APP_NAME')) require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : htmlspecialchars(APP_NAME); ?></title>
    <meta name="description" content="Fast, private & secure URL Shortener for personal link management with local history.">

    <!-- Local Fonts -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/poppins.css">

    <!-- App Styles -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/nav.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/footer.css">
</head>
<body>

<?php require_once __DIR__ . '/nav.php'; ?>
