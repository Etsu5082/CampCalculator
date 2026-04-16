<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName ?? '合宿費用計算アプリ') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="/public/css/style.css" rel="stylesheet">
</head>
<body>
    <?php if (Auth::check()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/"><?= htmlspecialchars($appName ?? 'サークル管理') ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/camps">
                            <i class="bi bi-house-gear"></i> 合宿管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/members">
                            <i class="bi bi-people"></i> 会員名簿
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/academic-years">
                            <i class="bi bi-calendar3"></i> 年度管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/guide">
                            <i class="bi bi-question-circle"></i> 使い方
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-outline-light btn-sm ms-2" onclick="logout()">
                            <i class="bi bi-box-arrow-right"></i> ログアウト
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container py-4">
        <?= $content ?>
    </main>

    <footer class="text-center text-muted py-3">
        <small>&copy; <?= date('Y') ?> サークル管理システム</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/js/app.js"></script>
</body>
</html>
