/* ─── Short URL — Main JS (LocalStorage & AJAX API) ───────── */

const STORAGE_KEY = 'short_url_history';

document.addEventListener('DOMContentLoaded', () => {
    renderHistory();
    fetchGitHubStarCount();

    const form = document.getElementById('shortenerForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (e) e.preventDefault();
            handleShorten(e);
            return false;
        });
    }
});

/**
 * Dynamically fetch real GitHub stargazers count for rafidahmed870/my-shortner
 */
function fetchGitHubStarCount() {
    fetch('https://api.github.com/repos/rafidahmed870/my-shortner')
        .then(res => res.json())
        .then(data => {
            if (typeof data.stargazers_count === 'number') {
                const countEls = document.querySelectorAll('.star-count');
                countEls.forEach(el => {
                    el.textContent = data.stargazers_count;
                });
            }
        })
        .catch(err => {
            console.error('Failed to fetch GitHub star count:', err);
        });
}

/**
 * Fetch history array from localStorage
 * @returns {Array}
 */
function getHistory() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    } catch (e) {
        console.error('Failed to parse localStorage history:', e);
        return [];
    }
}

/**
 * Save history array to localStorage
 * @param {Array} history
 */
function saveHistory(history) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(history));
    } catch (e) {
        console.error('Failed to save history to localStorage:', e);
    }
}

/**
 * Handle form submission and shorten URL via API
 */
function handleShorten(event) {
    if (event) {
        event.preventDefault();
        if (typeof event.stopPropagation === 'function') event.stopPropagation();
    }

    const longUrlInput = document.getElementById('longUrl');
    const submitBtn = document.getElementById('submitBtn');
    const resultBox = document.getElementById('resultBox');
    const shortenedUrl = document.getElementById('shortenedUrl');
    const visitBtn = document.getElementById('visitBtn');
    const pathId = document.getElementById('pathId');
    const originalUrlDisplay = document.getElementById('originalUrlDisplay');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');

    const urlValue = longUrlInput.value.trim();

    /* Basic client-side validation */
    if (!urlValue || (!urlValue.startsWith('http://') && !urlValue.startsWith('https://'))) {
        if (errorText) errorText.textContent = 'Please enter a valid URL starting with http:// or https://';
        errorMessage.classList.add('active');
        return;
    }
    errorMessage.classList.remove('active');

    /* Set loading state */
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;">
            <line x1="12" y1="2" x2="12" y2="6"/>
            <line x1="12" y1="18" x2="12" y2="22"/>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/>
            <line x1="2" y1="12" x2="6" y2="12"/>
            <line x1="18" y1="12" x2="22" y2="12"/>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/>
        </svg>
        <span>Shortening...</span>
    `;

    const formData = new FormData();
    formData.append('long_url', urlValue);
    formData.append('ajax', '1');

    // Submit to index.php directly
    fetch('index.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            shortenedUrl.textContent = data.short_url;
            pathId.textContent = data.code;
            visitBtn.href = data.short_url;
            if (originalUrlDisplay) originalUrlDisplay.textContent = data.original_url;
            
            resultBox.classList.add('active');
            showToast('Short URL generated successfully!');

            // Add item to local storage history
            addToHistory({
                code: data.code,
                short_url: data.short_url,
                original_url: data.original_url,
                created_at: data.created_at || new Date().toLocaleString()
            });

        } else {
            if (errorText) errorText.textContent = data.message || 'Something went wrong. Please try again.';
            errorMessage.classList.add('active');
        }
    })
    .catch(err => {
        console.error(err);
        if (errorText) errorText.textContent = 'Network or server error. Please try again.';
        errorMessage.classList.add('active');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
            <span>Shorten</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
            </svg>
        `;
    });
}
window.handleShorten = handleShorten;

/**
 * Add a new item to history in LocalStorage
 */
function addToHistory(item) {
    let history = getHistory();
    // Remove duplicate if code already exists
    history = history.filter(h => h.code !== item.code);
    // Unshift new item to beginning
    history.unshift(item);
    // Keep max 50 items
    if (history.length > 50) history.pop();
    
    saveHistory(history);
    renderHistory();
}

/**
 * Delete a specific item from history
 */
function deleteHistoryItem(code) {
    let history = getHistory();
    history = history.filter(item => item.code !== code);
    saveHistory(history);
    renderHistory();
    showToast('Link removed from history.');
}

/**
 * Clear all history items
 */
function clearHistory() {
    if (confirm('Are you sure you want to clear your local URL history?')) {
        saveHistory([]);
        renderHistory();
        showToast('Local history cleared.');
    }
}

/**
 * Render history UI dynamically
 */
function renderHistory() {
    const container = document.getElementById('historyContent');
    const countBadge = document.getElementById('historyCount');
    const clearBtn = document.getElementById('clearHistoryBtn');

    if (!container) return;

    const history = getHistory();

    if (countBadge) {
        countBadge.textContent = `${history.length} link${history.length === 1 ? '' : 's'}`;
    }

    if (clearBtn) {
        clearBtn.style.display = history.length > 0 ? 'inline-flex' : 'none';
    }

    if (history.length === 0) {
        container.innerHTML = `
            <div class="empty-history">
                <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <div class="empty-title">No Shortened Links Yet</div>
                <div class="empty-desc">Shorten your first link above and it will appear here in your browser history.</div>
            </div>
        `;
        return;
    }

    container.innerHTML = '';

    history.forEach(item => {
        const itemEl = document.createElement('div');
        itemEl.className = 'history-item';

        // Safe DOM creation preventing XSS
        const histLeft = document.createElement('div');
        histLeft.className = 'hist-left';

        const shortWrapper = document.createElement('div');
        shortWrapper.className = 'hist-short-wrapper';

        const shortLink = document.createElement('a');
        shortLink.className = 'hist-short';
        shortLink.href = item.short_url;
        shortLink.target = '_blank';
        shortLink.textContent = item.short_url;

        const dateSpan = document.createElement('span');
        dateSpan.className = 'hist-date';
        dateSpan.textContent = item.created_at ? new Date(item.created_at).toLocaleDateString() : '';

        shortWrapper.appendChild(shortLink);
        if (item.created_at) shortWrapper.appendChild(dateSpan);

        const origDiv = document.createElement('div');
        origDiv.className = 'hist-orig';
        origDiv.title = item.original_url;
        origDiv.textContent = item.original_url;

        histLeft.appendChild(shortWrapper);
        histLeft.appendChild(origDiv);

        // Actions
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'hist-actions';

        // Copy button
        const copyBtn = document.createElement('button');
        copyBtn.className = 'btn-icon-action';
        copyBtn.title = 'Copy short link';
        copyBtn.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
        `;
        copyBtn.onclick = () => copyToClipboardText(item.short_url);

        // Visit link button
        const visitBtn = document.createElement('a');
        visitBtn.className = 'btn-icon-action';
        visitBtn.href = item.short_url;
        visitBtn.target = '_blank';
        visitBtn.title = 'Open link';
        visitBtn.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15 3 21 3 21 9"/>
                <line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
        `;

        // Delete button
        const delBtn = document.createElement('button');
        delBtn.className = 'btn-icon-action btn-icon-delete';
        delBtn.title = 'Delete from history';
        delBtn.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            </svg>
        `;
        delBtn.onclick = () => deleteHistoryItem(item.code);

        actionsDiv.appendChild(copyBtn);
        actionsDiv.appendChild(visitBtn);
        actionsDiv.appendChild(delBtn);

        itemEl.appendChild(histLeft);
        itemEl.appendChild(actionsDiv);

        container.appendChild(itemEl);
    });
}

/**
 * Copy text to clipboard
 */
function copyToClipboardText(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
    } else {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }

    showToast('Copied to clipboard!');
}

/**
 * Copy result box URL to clipboard
 */
function copyToClipboard() {
    const text = document.getElementById('shortenedUrl').textContent;
    const copyBtnText = document.getElementById('copyBtnText');

    copyToClipboardText(text);

    if (copyBtnText) {
        copyBtnText.textContent = 'Copied!';
        setTimeout(() => { copyBtnText.textContent = 'Copy'; }, 2000);
    }
}

/**
 * Show toast alert
 */
function showToast(msg) {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    if (!toast) return;

    toastMessage.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}

/* CSS Keyframe for spin loading */
const style = document.createElement('style');
style.textContent = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
document.head.appendChild(style);