<?php
// デバッグ: セッションデータの確認
if (!isset($data) || empty($data)) {
    echo '<div class="alert alert-danger">';
    echo 'エラー: セッションデータがありません。入力画面からやり直してください。';
    echo '<br><a href="/enroll" class="btn btn-primary mt-2">入力画面に戻る</a>';
    echo '</div>';
    return;
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">入会申請内容の確認</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            入力内容を確認してください。間違いがなければ「申請する」ボタンをクリックしてください。
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>基本情報</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th style="width: 150px;">名前</th>
                        <td><?= htmlspecialchars($data['name_kanji']) ?>（<?= htmlspecialchars($data['name_kana']) ?>）</td>
                    </tr>
                    <tr>
                        <th>性別</th>
                        <td><?= $data['gender'] === 'male' ? '男性' : '女性' ?></td>
                    </tr>
                    <tr>
                        <th>生年月日</th>
                        <td><?= htmlspecialchars($data['birthdate']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>所属情報</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th style="width: 150px;">学籍番号</th>
                        <td><?= htmlspecialchars($data['student_id']) ?></td>
                    </tr>
                    <tr>
                        <th>学部</th>
                        <td><?= htmlspecialchars($data['faculty']) ?></td>
                    </tr>
                    <tr>
                        <th>学科/学系</th>
                        <td><?= htmlspecialchars($data['department']) ?></td>
                    </tr>
                    <tr>
                        <th>入学年度</th>
                        <td><?= htmlspecialchars($data['enrollment_year']) ?>年</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>連絡先</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th style="width: 150px;">電話番号</th>
                        <td><?= htmlspecialchars($data['phone']) ?></td>
                    </tr>
                    <tr>
                        <th>住所</th>
                        <td><?= nl2br(htmlspecialchars($data['address'])) ?></td>
                    </tr>
                    <tr>
                        <th>緊急連絡先</th>
                        <td><?= htmlspecialchars($data['emergency_contact']) ?></td>
                    </tr>
                    <?php if (!empty($data['email'])): ?>
                    <tr>
                        <th>メールアドレス</th>
                        <td><?= htmlspecialchars($data['email']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>LINE名</th>
                        <td><?= htmlspecialchars($data['line_name']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>その他</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th style="width: 150px;">アレルギー</th>
                        <td><?= !empty($data['allergy']) ? nl2br(htmlspecialchars($data['allergy'])) : 'なし' ?></td>
                    </tr>
                    <tr>
                        <th>SNS投稿</th>
                        <td><?= !empty($data['sns_allowed']) ? '可' : '不可' ?></td>
                    </tr>
                    <?php if (!empty($data['sports_registration_no'])): ?>
                    <tr>
                        <th>コート予約番号</th>
                        <td><?= htmlspecialchars($data['sports_registration_no']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <a href="/enroll" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> 修正する
            </a>
            <button type="button" class="btn btn-primary btn-lg flex-grow-1" onclick="submitEnrollment()">
                <i class="bi bi-check-circle"></i> 申請する
            </button>
        </div>
    </div>
</div>

<script>
function submitEnrollment() {
    if (!confirm('この内容で入会申請を送信します。よろしいですか？')) {
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 送信中...';

    // 申請送信
    fetch('/enroll', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=submit'
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('Result:', result);
        if (result.success) {
            window.location.href = result.redirect;
        } else {
            const errorMsg = result.error || result.errors || '送信に失敗しました';
            console.error('Server error:', errorMsg);
            alert(typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> 申請する';
        }
    })
    .catch(error => {
        console.error('送信エラー:', error);
        alert('送信に失敗しました: ' + error.message + '\n\nコンソールログを確認してください');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> 申請する';
    });
}
</script>
