/**
 * 合宿費用計算アプリ メインJavaScript
 */

// ログアウト処理
async function logout() {
    if (!confirm('ログアウトしますか？')) return;

    try {
        await fetch('/index.php?route=api/auth/logout', { method: 'POST' });
        window.location.href = '/index.php?route=login';
    } catch (err) {
        console.error(err);
        window.location.href = '/index.php?route=login';
    }
}

// URL生成ヘルパー
function appUrl(path) {
    return '/index.php?route=' + path.replace(/^\//, '');
}

// トースト表示
function showToast(message, type = 'success') {
    // 既存のトーストコンテナを取得または作成
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // トースト要素を作成
    const toast = document.createElement('div');
    toast.className = 'toast show';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="toast-body d-flex justify-content-between align-items-center">
            <span>${escapeHtml(message)}</span>
            <button type="button" class="btn-close btn-close-white ms-2" onclick="this.closest('.toast').remove()"></button>
        </div>
    `;

    // スタイル適用
    if (type === 'success') {
        toast.style.backgroundColor = '#198754';
        toast.style.color = 'white';
    } else if (type === 'error') {
        toast.style.backgroundColor = '#dc3545';
        toast.style.color = 'white';
    }

    container.appendChild(toast);

    // 3秒後に自動削除
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// HTMLエスケープ
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// 数値フォーマット
function formatNumber(num) {
    return Number(num).toLocaleString();
}

// 日付フォーマット
function formatDate(dateStr) {
    const d = new Date(dateStr);
    return `${d.getFullYear()}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getDate().toString().padStart(2, '0')}`;
}

// APIリクエストヘルパー
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        },
    };

    const mergedOptions = { ...defaultOptions, ...options };

    if (mergedOptions.body && typeof mergedOptions.body === 'object') {
        mergedOptions.body = JSON.stringify(mergedOptions.body);
    }

    const response = await fetch(url, mergedOptions);
    const data = await response.json();

    if (!data.success) {
        throw new Error(data.error?.message || 'リクエストに失敗しました');
    }

    return data.data;
}

// ページ読み込み完了時
document.addEventListener('DOMContentLoaded', () => {
    // 認証チェック（必要に応じて）
    // checkAuth();
});
