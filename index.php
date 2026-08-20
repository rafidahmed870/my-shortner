<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$message = '';
$postedResult = null;

// Handle POST request directly inside index.php (No separate API file required)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $longUrl = isset($_POST['long_url']) ? trim($_POST['long_url']) : (isset($_POST['url']) ? trim($_POST['url']) : '');

    if (empty($longUrl)) {
        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true);
        if (isset($jsonData['long_url'])) {
            $longUrl = trim($jsonData['long_url']);
        } elseif (isset($jsonData['url'])) {
            $longUrl = trim($jsonData['url']);
        }
    }

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || isset($_POST['ajax']);

    if (empty($longUrl)) {
        $errorMsg = 'Please enter a valid URL.';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
            exit;
        }
        $message = $errorMsg;
    } elseif (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
        $errorMsg = 'Invalid URL format. Please include http:// or https://';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
            exit;
        }
        $message = $errorMsg;
    } else {
        $parsedUrl = parse_url($longUrl);
        if (!isset($parsedUrl['scheme']) || !in_array(strtolower($parsedUrl['scheme']), ['http', 'https'], true)) {
            $errorMsg = 'Only HTTP and HTTPS URLs are permitted.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            $message = $errorMsg;
        } else {
            $db = getDB();
            if (!$db) {
                $errorMsg = 'Database connection failed. Please check server setup.';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit;
                }
                $message = $errorMsg;
            } else {
                try {
                    // Check if URL already exists
                    $stmt = $db->prepare("SELECT short_code, created_at FROM urls WHERE original_url = :url LIMIT 1");
                    $stmt->execute([':url' => $longUrl]);
                    $existing = $stmt->fetch();

                    $baseUrl = rtrim(defined('DYNAMIC_APP_URL') ? DYNAMIC_APP_URL : APP_URL, '/');

                    if ($existing) {
                        $shortCode = $existing['short_code'];
                        $shortUrl = $baseUrl . '/' . $shortCode;
                        $createdAt = $existing['created_at'];
                    } else {
                        // Generate unique short code
                        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                        $max = strlen($chars) - 1;
                        $attempts = 0;

                        do {
                            $attempts++;
                            $shortCode = '';
                            for ($i = 0; $i < 6; $i++) {
                                $shortCode .= $chars[random_int(0, $max)];
                            }
                            $checkStmt = $db->prepare("SELECT 1 FROM urls WHERE short_code = :code LIMIT 1");
                            $checkStmt->execute([':code' => $shortCode]);
                        } while ($checkStmt->fetch() && $attempts < 20);

                        $insertStmt = $db->prepare("INSERT INTO urls (short_code, original_url, clicks) VALUES (:code, :url, 0)");
                        $insertStmt->execute([
                            ':code' => $shortCode,
                            ':url' => $longUrl
                        ]);

                        $shortUrl = $baseUrl . '/' . $shortCode;
                        $createdAt = date('Y-m-d H:i:s');
                    }

                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode([
                            'success' => true,
                            'code' => $shortCode,
                            'short_url' => $shortUrl,
                            'original_url' => $longUrl,
                            'created_at' => $createdAt
                        ]);
                        exit;
                    }

                    $postedResult = [
                        'code' => $shortCode,
                        'short_url' => $shortUrl,
                        'original_url' => $longUrl,
                        'created_at' => $createdAt
                    ];

                } catch (Exception $e) {
                    error_log("Shorten Error: " . $e->getMessage());
                    $errorMsg = 'An error occurred while shortening the URL.';
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['success' => false, 'message' => $errorMsg]);
                        exit;
                    }
                    $message = $errorMsg;
                }
            }
        }
    }
}

$pageTitle = APP_NAME . ' | Fast, Private & Local History URL Shortener';
require_once __DIR__ . '/functions/header.php';
?>

<main class="page-main">

    <!-- Hero Section -->
    <div class="hero-text">
        <div class="pill-tag">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>No Auth Required &bull; Local Browser History</span>
        </div>
        <h1>
            Shorten Your Links <br>
            <span class="full-gradient-text">Private, Fast & Secure</span>
        </h1>
        <p>
            Convert long, clunky links into clean short URLs instantly.
            Your history stays privately stored right in your browser!
        </p>
    </div>

    <!-- Shortener Card -->
    <div class="card-box">
        <form id="shortenerForm" method="POST" action="<?php echo APP_URL; ?>">
            <div class="input-group">
                <div class="input-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                    <input
                        type="url"
                        id="longUrl"
                        name="long_url"
                        required
                        placeholder="Paste your long URL here (https://...)"
                        class="url-input"
                        autocomplete="off"
                        value="<?php echo isset($_POST['long_url']) ? htmlspecialchars($_POST['long_url']) : ''; ?>"
                    >
                </div>
                <button type="submit" id="submitBtn" class="submit-btn">
                    <span>Shorten</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </div>
            <div id="errorMessage" class="error-text <?php echo !empty($message) ? 'active' : ''; ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="errorText"><?php echo !empty($message) ? htmlspecialchars($message) : 'Please enter a valid URL starting with http:// or https://'; ?></span>
            </div>
        </form>

        <!-- Result Box -->
        <div id="resultBox" class="result-box <?php echo $postedResult ? 'active' : ''; ?>">
            <div class="result-header">
                <span>Your Shortened Link:</span>
                <span class="status-ready">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>Link Ready</span>
                </span>
            </div>

            <div class="short-url-wrapper">
                <div class="short-url-text" id="shortenedUrl">
                    <?php echo $postedResult ? htmlspecialchars($postedResult['short_url']) : rtrim(APP_URL, '/') . '/code'; ?>
                </div>
                <div class="action-btns">
                    <button onclick="copyToClipboard()" id="copyBtn" class="btn-copy">
                        <svg class="btn-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        <span id="copyBtnText">Copy</span>
                    </button>
                    <a id="visitBtn" href="<?php echo $postedResult ? htmlspecialchars($postedResult['short_url']) : '#'; ?>" target="_blank" class="btn-visit" title="Open Link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="path-info">
                <span>Short Code: <strong id="pathId" class="path-id"><?php echo $postedResult ? htmlspecialchars($postedResult['code']) : 'code'; ?></strong></span>
                <span>Original URL: <span id="originalUrlDisplay" class="orig-link-text"><?php echo $postedResult ? htmlspecialchars($postedResult['original_url']) : ''; ?></span></span>
            </div>
        </div>
    </div>

    <!-- LocalStorage History Section -->
    <div class="history-card" id="historySection">
        <div class="history-header">
            <div class="history-title-group">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="history-icon">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <h2>Your Link History</h2>
                <span class="history-badge" id="historyCount">0 links</span>
            </div>
            
            <div class="history-controls">
                <button onclick="clearHistory()" class="btn-clear-history" id="clearHistoryBtn" title="Clear all local history">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    <span>Clear All</span>
                </button>
            </div>
        </div>

        <div id="historyContent" class="history-content">
            <!-- Rendered by JavaScript from localStorage -->
        </div>
    </div>

    <!-- Feature Cards -->
    <div class="features-grid">
        <div class="feature-item">
            <div class="feature-icon icon-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div>
                <div class="feature-title">Secure &amp; Permanent</div>
                <div class="feature-desc">Links are stored safely in MySQL with unique short codes.</div>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon icon-orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
            </div>
            <div>
                <div class="feature-title">Lightning Fast Redirect</div>
                <div class="feature-desc">Instant HTTP 301 redirection without delay or ads.</div>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon icon-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div>
                <div class="feature-title">Local Browser History</div>
                <div class="feature-desc">Your generated links are saved directly to your browser's localStorage.</div>
            </div>
        </div>
    </div>

</main>

<?php if ($postedResult): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof addToHistory === 'function') {
        addToHistory(<?php echo json_encode($postedResult); ?>);
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/functions/footer.php'; ?>
