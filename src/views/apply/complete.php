<?php
$pageTitle = '申し込み完了';
$appName = '合宿申し込み';

// 参加日程の表示用テキスト（管理システムと同じ）
$joinTimingLabels = [
    'outbound_bus' => '往路バス',
    'breakfast' => '朝食',
    'morning' => '午前イベント',
    'lunch' => '昼食',
    'afternoon' => '午後イベント',
    'dinner' => '夕食',
    'night' => '夜',
    'lodging' => '宿泊',
];

$leaveTimingLabels = [
    'before_breakfast' => '朝食前',
    'breakfast' => '朝食',
    'morning' => '午前イベント',
    'lunch' => '昼食',
    'afternoon' => '午後イベント',
    'dinner' => '夕食',
    'night' => '夜',
    'return_bus' => '復路バス',
];

ob_start();
?>

<div class="card shadow">
    <div class="card-body p-4 text-center">
        <div class="mb-4">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        </div>

        <h2 class="mb-3">申し込みを受け付けました</h2>

        <div class="alert alert-success">
            <strong><?= htmlspecialchars($member['name_kanji']) ?></strong> さんの申し込みを受け付けました。
        </div>

        <?php if (!empty($member['email'])): ?>
        <p class="text-muted mb-4">
            <i class="bi bi-envelope"></i>
            確認メールを <strong><?= htmlspecialchars($member['email']) ?></strong> に送信しました。
        </p>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow mt-3">
    <div class="card-header bg-light">
        <strong>申し込み内容</strong>
    </div>
    <div class="card-body">
        <h6 class="mb-3"><?= htmlspecialchars($camp['name']) ?></h6>

        <table class="table table-sm table-borderless">
            <tr>
                <th width="120">参加期間:</th>
                <td>
                    <?= $application['join_day'] ?>日目 <?= $joinTimingLabels[$application['join_timing']] ?? $application['join_timing'] ?>
                    〜
                    <?= $application['leave_day'] ?>日目 <?= $leaveTimingLabels[$application['leave_timing']] ?? $application['leave_timing'] ?>
                </td>
            </tr>
            <tr>
                <th>往路バス:</th>
                <td><?= $application['use_outbound_bus'] ? '利用する' : '利用しない' ?></td>
            </tr>
            <tr>
                <th>復路バス:</th>
                <td><?= $application['use_return_bus'] ? '利用する' : '利用しない' ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="alert alert-warning mt-3">
    <h6><i class="bi bi-exclamation-triangle"></i> 変更・キャンセルについて</h6>
    <p class="mb-0">
        申し込み後の変更・キャンセルは、幹事に連絡してください。<br>
        管理者の承認後に変更が反映されます。
    </p>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/public.php';
?>
