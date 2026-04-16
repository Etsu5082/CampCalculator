<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">継続入会フォーム（<?= htmlspecialchars($currentYear['year']) ?>年度） - 情報確認</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            以下の内容で<?= htmlspecialchars($currentYear['year']) ?>年度に継続登録します。<br>
            変更がある項目のみ修正してください。
        </div>

        <form id="renewalForm">
            <!-- 基本情報 -->
            <h5 class="border-bottom pb-2 mb-3">基本情報</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name_kanji" class="form-label">名前（漢字）<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name_kanji" name="name_kanji"
                           value="<?= htmlspecialchars($member['name_kanji']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="name_kana" class="form-label">名前（カナ）<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name_kana" name="name_kana"
                           value="<?= htmlspecialchars($member['name_kana']) ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">性別<span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male"
                                   <?= $member['gender'] === 'male' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="gender_male">男性</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female"
                                   <?= $member['gender'] === 'female' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="gender_female">女性</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="grade" class="form-label">学年<span class="text-danger">*</span></label>
                    <select class="form-select" id="grade" name="grade" required>
                        <option value="1" <?= $nextGrade === '1' ? 'selected' : '' ?>>1年</option>
                        <option value="2" <?= $nextGrade === '2' ? 'selected' : '' ?>>2年</option>
                        <option value="3" <?= $nextGrade === '3' ? 'selected' : '' ?>>3年</option>
                        <option value="4" <?= $nextGrade === '4' ? 'selected' : '' ?>>4年</option>
                        <option value="M1" <?= $nextGrade === 'M1' ? 'selected' : '' ?>>M1</option>
                        <option value="M2" <?= $nextGrade === 'M2' ? 'selected' : '' ?>>M2</option>
                        <option value="OB" <?= $nextGrade === 'OB' ? 'selected' : '' ?>>OB</option>
                        <option value="OG" <?= $nextGrade === 'OG' ? 'selected' : '' ?>>OG</option>
                    </select>
                    <small class="text-muted">自動的に+1されています（編集可能）</small>
                </div>
            </div>

            <!-- 所属情報 -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">所属情報</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="student_id" class="form-label">学籍番号<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="student_id" name="student_id"
                           value="<?= htmlspecialchars($member['student_id']) ?>" readonly>
                    <small class="text-muted">変更不可</small>
                </div>
                <div class="col-md-6">
                    <label for="birthdate" class="form-label">生年月日<span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="birthdate" name="birthdate"
                           value="<?= htmlspecialchars($member['birthdate']) ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="faculty" class="form-label">学部<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="faculty" name="faculty"
                           value="<?= htmlspecialchars($member['faculty']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="department" class="form-label">学科<span class="text-danger">*</span></label>
                    <?php
                    $isKikanNew2 = ($member['faculty'] === '基幹理工学部' && $nextGrade === '2');
                    $kikanDepartments = [
                        '数学科',
                        '応用数理学科',
                        '機械科学・航空宇宙学科',
                        '電子物理システム学科',
                        '情報理工学科',
                        '情報通信学科',
                        '表現工学科',
                    ];
                    ?>
                    <?php if ($isKikanNew2): ?>
                    <select class="form-select" id="department" name="department" required>
                        <option value="">-- 学科を選択してください --</option>
                        <?php foreach ($kikanDepartments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>">
                            <?= htmlspecialchars($dept) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-warning fw-bold">
                        <i class="bi bi-exclamation-triangle"></i>
                        基幹理工学部2年生は進振り後の学科を選択してください
                    </small>
                    <?php else: ?>
                    <input type="text" class="form-control" id="department" name="department"
                           value="<?= htmlspecialchars($member['department']) ?>" required>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 連絡先 -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">連絡先</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">電話番号<span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           value="<?= htmlspecialchars($member['phone']) ?>"
                           placeholder="090-1234-5678" required>
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact" class="form-label">緊急連絡先<span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact"
                           value="<?= htmlspecialchars($member['emergency_contact']) ?>"
                           placeholder="090-1234-5678" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">住所<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="address" name="address"
                       value="<?= htmlspecialchars($member['address']) ?>"
                       placeholder="東京都新宿区..." required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="line_name" class="form-label">LINE名<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="line_name" name="line_name"
                           value="<?= htmlspecialchars($member['line_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($member['email'] ?? '') ?>"
                           placeholder="example@waseda.jp">
                    <small class="text-muted">任意</small>
                </div>
            </div>

            <!-- その他 -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">その他</h5>

            <div class="mb-3">
                <label for="allergy" class="form-label">アレルギー</label>
                <textarea class="form-control" id="allergy" name="allergy" rows="2"
                          placeholder="特になければ空欄で構いません"><?= htmlspecialchars($member['allergy'] ?? '') ?></textarea>
                <small class="text-muted">任意</small>
            </div>

            <div class="mb-3">
                <label for="sports_registration_no" class="form-label">コート予約番号</label>
                <input type="text" class="form-control" id="sports_registration_no" name="sports_registration_no"
                       value="<?= htmlspecialchars($member['sports_registration_no'] ?? '') ?>">
                <small class="text-muted">東京都スポーツ施設予約システムの8桁の番号（任意）</small>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="sns_allowed" name="sns_allowed" value="1"
                       <?= ($member['sns_allowed'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="sns_allowed">
                    SNS掲載許可（合宿等の写真をSNSに投稿することを許可する）
                </label>
            </div>

            <!-- ボタン -->
            <div class="d-flex justify-content-between mt-4">
                <a href="/renew" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 戻る
                </a>
                <button type="submit" class="btn btn-success">
                    確認画面へ <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('renewalForm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // フォームデータを取得
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();

        // 確認画面に遷移
        window.location.href = '/renew/review?' + params;
    });
});
</script>
