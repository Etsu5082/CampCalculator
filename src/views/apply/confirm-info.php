<?php
$pageTitle = htmlspecialchars($camp['name']) . ' - 情報確認';
$appName = '合宿申し込み';

ob_start();
?>

<div class="card shadow">
    <div class="card-body p-4">
        <h2 class="text-center mb-4"><?= htmlspecialchars($camp['name']) ?></h2>

        <h5 class="mb-3">以下の情報で申し込みを行います</h5>
        <p class="text-muted mb-4">内容に間違いがないか確認してください。</p>

        <?php if ($hasApplied): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            既にこの合宿に申し込み済みです。再度申し込むと、前の申し込み内容が上書きされます。
        </div>
        <?php endif; ?>

        <div class="card bg-light mb-4">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="bi bi-check-circle text-success"></i>
                    この情報で間違いありませんか？
                </h6>

                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th width="120">名前:</th>
                            <td><?= htmlspecialchars($member['name_kanji']) ?></td>
                        </tr>
                        <tr>
                            <th>学籍番号:</th>
                            <td><?= htmlspecialchars($member['student_id']) ?></td>
                        </tr>
                        <tr>
                            <th>学年:</th>
                            <td><?= htmlspecialchars($member['grade']) ?>年</td>
                        </tr>
                        <tr>
                            <th>性別:</th>
                            <td><?= $member['gender'] === 'male' ? '男性' : '女性' ?></td>
                        </tr>
                        <tr>
                            <th>学部:</th>
                            <td><?= htmlspecialchars($member['faculty']) ?></td>
                        </tr>
                        <tr>
                            <th>学科:</th>
                            <td><?= htmlspecialchars($member['department']) ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-warning mb-0 mt-3">
                    <small>
                        <i class="bi bi-exclamation-triangle"></i>
                        情報に誤りがある場合は、幹事に連絡してください。
                    </small>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> 戻る
            </button>
            <button type="button" class="btn btn-primary flex-grow-1" onclick="goToSchedule()">
                情報に間違いなし <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<script>
function goToSchedule() {
    window.location.href = `/apply/<?= $token ?>/schedule?member_id=<?= $member['id'] ?>`;
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/public.php';
?>
