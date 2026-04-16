<?php
// セッションからの復元データ（確認画面から戻った場合）
$d = $savedData ?? [];
?>
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">サークル 入会申請フォーム</h4>
    </div>
    <div class="card-body">

        <?php if (!empty($enrollmentClosed)): ?>
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-x-circle fs-1 d-block mb-3"></i>
            <h5>現在、入会受付を行っていません</h5>
            <p class="mb-0">入会受付期間についてはサークルにお問い合わせください。</p>
        </div>
        <?php return; ?>
        <?php endif; ?>

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            新入生の方は以下のフォームに入力してください。すべての項目が必須です（アレルギーは任意）。
        </div>

        <form id="enrollmentForm">
            <!-- 基本情報 -->
            <h5 class="border-bottom pb-2 mb-3">基本情報</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name_kanji" class="form-label">名前（漢字）<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name_kanji" name="name_kanji"
                           placeholder="例: 山田 太郎" value="<?= htmlspecialchars($d['name_kanji'] ?? '') ?>" required>
                    <small class="text-muted">全角スペース区切り</small>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6">
                    <label for="name_kana" class="form-label">名前（カナ）<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name_kana" name="name_kana"
                           placeholder="例: ヤマダ タロウ" value="<?= htmlspecialchars($d['name_kana'] ?? '') ?>" required>
                    <small class="text-muted">全角スペース区切り</small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">性別<span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" <?= ($d['gender'] ?? '') === 'male' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="gender_male">男性</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" <?= ($d['gender'] ?? '') === 'female' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="gender_female">女性</label>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6">
                    <label for="birthdate" class="form-label">生年月日<span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= htmlspecialchars($d['birthdate'] ?? '') ?>" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- 所属情報 -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">所属情報</h5>

            <div class="mb-3">
                <label for="student_id" class="form-label">学籍番号<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="student_id" name="student_id"
                       placeholder="例: 1Y25F158-5" value="<?= htmlspecialchars($d['student_id'] ?? '') ?>" required>
                <small class="text-muted">CDあり（例: 1Y<?= date('y') ?>F158-5）</small>
                <div class="invalid-feedback"></div>
            </div>

            <div class="alert alert-secondary">
                <strong>学籍番号を入力すると自動で以下が判定されます</strong>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">学部</label>
                    <input type="text" class="form-control" id="faculty" name="faculty" value="<?= htmlspecialchars($d['faculty'] ?? '') ?>" readonly>
                    <small class="text-muted">自動判定</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">入学年度</label>
                    <input type="text" class="form-control" id="enrollment_year" name="enrollment_year" value="<?= htmlspecialchars($d['enrollment_year'] ?? '') ?>" readonly>
                    <small class="text-muted">自動判定</small>
                </div>
                <div class="col-md-4" id="department_auto_col">
                    <label class="form-label">学科</label>
                    <input type="text" class="form-control" id="department_auto" name="department_auto" value="<?= htmlspecialchars($d['department'] ?? '') ?>" readonly>
                    <small class="text-muted">自動判定</small>
                </div>
            </div>

            <!-- 基幹理工学部の学系・学科選択（条件付き表示） -->
            <div id="kikan_gakukei_section" class="mb-3" style="display: none;">
                <div class="alert alert-warning" id="kikan_gakukei_alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span id="kikan_gakukei_alert_text">基幹理工学部の場合は学系を選択してください</span>
                </div>
                <label for="department_select" class="form-label" id="kikan_gakukei_label">学系<span class="text-danger">*</span></label>
                <select class="form-select" id="department_select" name="department">
                    <option value="">選択してください</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <input type="hidden" id="department" name="department" value="<?= htmlspecialchars($d['department'] ?? '') ?>">

            <!-- 連絡先 -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">連絡先</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">電話番号<span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           placeholder="例: 090-1234-5678" value="<?= htmlspecialchars($d['phone'] ?? '') ?>" required>
                    <small class="text-muted">ハイフンあり</small>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact" class="form-label">緊急連絡先<span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact"
                           placeholder="例: 03-1234-5678" value="<?= htmlspecialchars($d['emergency_contact'] ?? '') ?>" required>
                    <small class="text-muted">保護者など</small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">住所<span class="text-danger">*</span></label>
                <textarea class="form-control" id="address" name="address" rows="2"
                          placeholder="現住所を入力してください" required><?= htmlspecialchars($d['address'] ?? '') ?></textarea>
                <div class="invalid-feedback"></div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="example@email.com" value="<?= htmlspecialchars($d['email'] ?? '') ?>">
                    <small class="text-muted">任意</small>
                </div>
                <div class="col-md-6">
                    <label for="line_name" class="form-label">現在のLINE名<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="line_name" name="line_name" value="<?= htmlspecialchars($d['line_name'] ?? '') ?>" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- その他 -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">その他</h5>

            <div class="mb-3">
                <label for="allergy" class="form-label">アレルギー</label>
                <textarea class="form-control" id="allergy" name="allergy" rows="2"
                          placeholder="アレルギーがある場合は記入してください"><?= htmlspecialchars($d['allergy'] ?? '') ?></textarea>
                <small class="text-muted">任意項目</small>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="sns_allowed" name="sns_allowed" value="1" <?= (!isset($d['sns_allowed']) || $d['sns_allowed']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="sns_allowed">
                        活動写真をSNSに投稿してもよい
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label for="sports_registration_no" class="form-label">コート予約番号</label>
                <input type="text" class="form-control" id="sports_registration_no" name="sports_registration_no"
                       placeholder="8桁の番号" maxlength="8" value="<?= htmlspecialchars($d['sports_registration_no'] ?? '') ?>">
                <small class="text-muted">東京都スポーツ施設予約システムの8桁の番号（任意）</small>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> 確認画面へ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// セッションから復元されたデータがあるか
const savedData = <?= json_encode($d) ?>;

// ページ読み込み時に復元データがあれば、基幹理工学部の学系選択を処理
document.addEventListener('DOMContentLoaded', function() {
    if (savedData && savedData.student_id && savedData.faculty === '基幹理工学部') {
        // 基幹理工学部の場合は学科欄を非表示にして学系・学科選択を表示
        document.getElementById('department_auto_col').style.display = 'none';
        showKikanGakukeiSelection(savedData.enrollment_year);
        // 保存された学系を選択
        setTimeout(() => {
            const select = document.getElementById('department_select');
            if (select && savedData.department) {
                select.value = savedData.department;
            }
        }, 100);
    }
});

// 学籍番号の自動判定
document.getElementById('student_id').addEventListener('blur', function() {
    const studentId = this.value.trim();
    if (!studentId) return;

    // API呼び出しで学籍番号を解析
    fetch('/api/members/parse-student-id?student_id=' + encodeURIComponent(studentId))
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data.is_valid) {
                const data = result.data;

                // 学部・入学年度を自動入力
                document.getElementById('faculty').value = data.faculty || '';
                document.getElementById('enrollment_year').value = data.enrollment_year || '';

                // 基幹理工学部の場合は学系選択を表示
                if (data.needs_department_selection) {
                    document.getElementById('department_auto_col').style.display = 'none';
                    showKikanGakukeiSelection(data.enrollment_year);
                } else {
                    // 創造・先進理工学部の場合は学科を自動入力
                    document.getElementById('department_auto_col').style.display = '';
                    document.getElementById('department_auto').value = data.department || '';
                    document.getElementById('department').value = data.department || '';
                    hideKikanGakukeiSelection();
                }
            } else {
                alert('学籍番号の形式が正しくありません。\n例: 1Y25F158-5');
            }
        })
        .catch(error => {
            console.error('学籍番号解析エラー:', error);
        });
});

// 基幹理工学部の学系・学科選択を表示
function showKikanGakukeiSelection(enrollmentYear) {
    const section = document.getElementById('kikan_gakukei_section');
    const select = document.getElementById('department_select');
    const label = document.getElementById('kikan_gakukei_label');
    const alertText = document.getElementById('kikan_gakukei_alert_text');

    section.style.display = 'block';
    select.innerHTML = '<option value="">選択してください</option>';

    // 現在の年度（4月以降は当年、3月以前は前年）
    const now = new Date();
    const currentAcademicYear = now.getMonth() >= 3 ? now.getFullYear() : now.getFullYear() - 1;
    const yearsEnrolled = currentAcademicYear - enrollmentYear + 1;

    let options = [];

    if (yearsEnrolled >= 2) {
        // 2年生以上: 進振り後の学科を選択
        label.innerHTML = '学科<span class="text-danger">*</span>';
        alertText.textContent = '基幹理工学部2年生以上は進振り後の学科を選択してください';
        options = [
            '数学科',
            '応用数理学科',
            '機械科学・航空宇宙学科',
            '電子物理システム学科',
            '情報理工学科',
            '情報通信学科',
            '表現工学科',
        ];
    } else {
        // 1年生: 学系を選択
        label.innerHTML = '学系<span class="text-danger">*</span>';
        alertText.textContent = '基幹理工学部の場合は学系を選択してください';
        if (enrollmentYear >= 2025) {
            options = [
                '学系I（数学系）',
                '学系II（工学系）',
                '学系III（情報系）',
                '学系IV（メディア系）'
            ];
        } else {
            options = [
                '学系I',
                '学系II',
                '学系III'
            ];
        }
    }

    options.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt;
        option.textContent = opt;
        select.appendChild(option);
    });

    // 選択されたら隠しフィールドに反映
    select.onchange = function() {
        document.getElementById('department').value = this.value;
    };
}

// 基幹理工学部の学系選択を非表示
function hideKikanGakukeiSelection() {
    document.getElementById('kikan_gakukei_section').style.display = 'none';
}

// フォーム送信処理
document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // バリデーション
    const form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    // フォームデータを収集
    const formData = new FormData(form);
    formData.append('action', 'confirm');

    // 送信
    fetch('/enroll', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = result.redirect;
        } else {
            // エラー表示
            if (result.errors) {
                showValidationErrors(result.errors);
            } else {
                alert(result.error || '送信に失敗しました');
            }
        }
    })
    .catch(error => {
        console.error('送信エラー:', error);
        alert('送信に失敗しました');
    });
});

// バリデーションエラーの表示
function showValidationErrors(errors) {
    // すべてのエラーをクリア
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });

    // エラーを表示
    for (const [field, message] of Object.entries(errors)) {
        const input = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = message;
            }
        }
    }

    // 最初のエラー箇所にスクロール
    const firstError = document.querySelector('.is-invalid');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
