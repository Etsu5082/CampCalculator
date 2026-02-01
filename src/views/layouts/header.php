<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">合宿費用計算アプリ</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">
                        <i class="bi bi-house-door"></i> ホーム
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pdf/upload">
                        <i class="bi bi-file-pdf"></i> PDF読み取り
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/guide">
                        <i class="bi bi-question-circle"></i> 使い方
                    </a>
                </li>
                <?php if (Auth::check()): ?>
                <li class="nav-item">
                    <button class="btn btn-outline-light btn-sm ms-2" onclick="logout()">
                        <i class="bi bi-box-arrow-right"></i> ログアウト
                    </button>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
function logout() {
    if (confirm('ログアウトしますか？')) {
        fetch('/api/auth/logout', {
            method: 'POST',
        }).then(() => {
            window.location.href = '/login';
        });
    }
}
</script>
