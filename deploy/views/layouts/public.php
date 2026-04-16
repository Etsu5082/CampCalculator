<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName ?? 'サークル管理システム') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="/public/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .public-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-light border-bottom">
        <div class="container">
            <a class="navbar-brand" href="/portal">
                <i class="bi bi-house-door"></i> 会員ポータル
            </a>
        </div>
    </nav>

    <main class="public-container">
        <?= $content ?>
    </main>

    <footer class="text-center text-muted py-3 mt-5">
        <small>&copy; <?= date('Y') ?> サークル管理システム</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
