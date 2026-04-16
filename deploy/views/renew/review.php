<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">継続入会フォーム（<?= htmlspecialchars($currentYear) ?>年度） - 最終確認</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            以下の内容で登録します。内容を確認してください。
        </div>

        <!-- 基本情報 -->
        <h5 class="border-bottom pb-2 mb-3">基本情報</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>名前（漢字）:</strong> <?= htmlspecialchars($member['name_kanji']) ?>
            </div>
            <div class="col-md-6">
                <strong>名前（カナ）:</strong> <?= htmlspecialchars($member['name_kana']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>性別:</strong> <?= $member['gender'] === 'male' ? '男性' : '女性' ?>
            </div>
            <div class="col-md-6">
                <strong>学年:</strong>
                <?php
                $gradeDisplay = $member['grade'];
                if (in_array($gradeDisplay, ['1', '2', '3', '4'])) {
                    $gradeDisplay .= '年';
                }
                echo htmlspecialchars($gradeDisplay);
                ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <strong>生年月日:</strong> <?= htmlspecialchars($member['birthdate']) ?>
            </div>
        </div>

        <!-- 所属情報 -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">所属情報</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>学籍番号:</strong> <?= htmlspecialchars($member['student_id']) ?>
            </div>
            <div class="col-md-6">
                <strong>学部:</strong> <?= htmlspecialchars($member['faculty']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <strong>学科:</strong> <?= htmlspecialchars($member['department']) ?>
            </div>
        </div>

        <!-- 連絡先 -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">連絡先</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>電話番号:</strong> <?= htmlspecialchars($member['phone']) ?>
            </div>
            <div class="col-md-6">
                <strong>緊急連絡先:</strong> <?= htmlspecialchars($member['emergency_contact']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <strong>住所:</strong> <?= htmlspecialchars($member['address']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>LINE名:</strong> <?= htmlspecialchars($member['line_name']) ?>
            </div>
            <div class="col-md-6">
                <strong>メールアドレス:</strong> <?= htmlspecialchars($member['email'] ?? '未登録') ?>
            </div>
        </div>

        <!-- その他 -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">その他</h5>
        <div class="row mb-3">
            <div class="col-md-12">
                <strong>アレルギー:</strong>
                <?= !empty($member['allergy']) ? htmlspecialchars($member['allergy']) : 'なし' ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <strong>コート予約番号:</strong>
                <?= !empty($member['sports_registration_no']) ? htmlspecialchars($member['sports_registration_no']) : '未登録' ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <strong>SNS掲載許可:</strong>
                <?= ($member['sns_allowed'] ?? 1) ? '許可する' : '許可しない' ?>
            </div>
        </div>

        <!-- ボタン -->
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> 戻って修正
            </button>
            <button type="button" class="btn btn-success" id="submitBtn">
                <i class="bi bi-check-circle"></i> この内容で登録する
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.addEventListener('click', async function() {
        if (!confirm('<?= htmlspecialchars($currentYear) ?>年度の継続入会を確定します。よろしいですか？')) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 登録中...';

        try {
            const response = await fetch('/api/renew/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (!data.success) {
                alert(data.error || '登録に失敗しました');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> この内容で登録する';
                return;
            }

            // 完了画面に遷移
            window.location.href = data.redirect;

        } catch (error) {
            console.error('Error:', error);
            alert('登録に失敗しました');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> この内容で登録する';
        }
    });
});
</script>
