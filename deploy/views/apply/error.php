<?php
$pageTitle = 'エラー';
$appName = '合宿申し込み';

ob_start();
?>

<div class="card shadow">
    <div class="card-body p-4 text-center">
        <div class="mb-4">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
        </div>

        <h2 class="mb-3">エラー</h2>

        <div class="alert alert-danger">
            <?= htmlspecialchars($errorMessage) ?>
        </div>

        <p class="text-muted">
            問題が解決しない場合は、幹事に連絡してください。
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/public.php';
?>
