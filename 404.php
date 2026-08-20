<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config/config.php';
}
$pageTitle = '404 - Page Not Found | ' . APP_NAME;
require_once __DIR__ . '/functions/header.php';
?>

<main class="page-main error-page">

    <div class="error-container">
        <!-- SVG Graphic for 404 -->
        <div class="error-illustration">
            <svg viewBox="0 0 240 180" width="240" height="180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="120" cy="90" r="70" fill="url(#errorGlow)" fill-opacity="0.15"/>
                <path d="M70 120 C 70 80, 170 80, 170 120" stroke="#8C52FF" stroke-width="4" stroke-dasharray="6 6" stroke-linecap="round"/>
                <rect x="50" y="45" width="140" height="90" rx="16" fill="#181528" stroke="#322C4A" stroke-width="2"/>
                <path d="M 80 80 C 80 70, 95 70, 95 80 C 95 90, 80 90, 80 100" stroke="#FF7A00" stroke-width="4" stroke-linecap="round" fill="none"/>
                <circle cx="87.5" cy="112" r="3" fill="#FF7A00"/>
                <text x="115" y="105" font-family="system-ui, sans-serif" font-size="42" font-weight="800" fill="#FFFFFF">404</text>
                <defs>
                    <radialGradient id="errorGlow" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(120 90) rotate(90) scale(70)">
                        <stop stop-color="#8C52FF"/>
                        <stop offset="1" stop-color="#FF7A00" stop-opacity="0"/>
                    </radialGradient>
                </defs>
            </svg>
        </div>

        <div class="error-content">
            <div class="pill-tag warning-tag">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>Link Not Found or Expired</span>
            </div>

            <h1 class="error-title">Oops! Short Link Not Found</h1>
            <p class="error-desc">
                The link you are trying to access does not exist, was removed, or has an invalid short code.
            </p>

            <div class="error-actions">
                <a href="<?php echo APP_URL; ?>" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    <span>Back to Shortener</span>
                </a>
            </div>
        </div>

    </div>

</main>

<?php require_once __DIR__ . '/functions/footer.php'; ?>
