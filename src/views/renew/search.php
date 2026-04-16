<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">継続入会フォーム<?= $currentYear ? '（' . htmlspecialchars($currentYear['year']) . '年度）' : '' ?></h4>
    </div>
    <div class="card-body">
        <?php if (!$currentYear): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            現在、入会受付を行っていません。
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            前年度（<?= htmlspecialchars($previousYear) ?>年度）の情報から継続登録します。<br>
            名前を入力して検索してください。
        </div>
        <?php endif; ?>

        <!-- 名前検索フォーム -->
        <div class="mb-4">
            <label for="searchName" class="form-label">名前を入力</label>
            <div class="input-group">
                <input type="text" class="form-control" id="searchName"
                       placeholder="例: 山田太郎 または ヤマダタロウ">
                <button class="btn btn-primary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i> 検索
                </button>
            </div>
            <small class="text-muted">カナまたは漢字で検索できます（部分一致）</small>
        </div>

        <!-- 検索結果 -->
        <div id="searchResults" style="display: none;">
            <h5 class="border-bottom pb-2 mb-3">検索結果</h5>
            <div id="resultsList"></div>
        </div>

        <!-- 新規入会案内 -->
        <div class="mt-4 pt-3 border-top">
            <p class="text-muted">
                <i class="bi bi-question-circle"></i>
                名前が見つからない場合は、<a href="/enroll" class="fw-bold">新規入会フォーム</a>からお申し込みください。
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('searchBtn');
    const searchName = document.getElementById('searchName');
    const searchResults = document.getElementById('searchResults');
    const resultsList = document.getElementById('resultsList');

    // Enterキーで検索
    searchName.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // 検索ボタン
    searchBtn.addEventListener('click', async function() {
        const name = searchName.value.trim();

        if (!name) {
            alert('名前を入力してください');
            return;
        }

        searchBtn.disabled = true;
        searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 検索中...';

        try {
            const response = await fetch('/api/renew/search-members?name=' + encodeURIComponent(name));
            const data = await response.json();

            if (!data.success) {
                alert(data.error || '検索に失敗しました');
                return;
            }

            displayResults(data.members, data.currentYear);

        } catch (error) {
            console.error('Error:', error);
            alert('検索に失敗しました');
        } finally {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="bi bi-search"></i> 検索';
        }
    });

    // 検索結果を表示
    function displayResults(members, currentYear) {
        if (members.length === 0) {
            resultsList.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    該当する会員が見つかりませんでした。
                </div>
            `;
        } else {
            resultsList.innerHTML = members.map(member => {
                const gradeDisplay = member.grade === '0'
                    ? (member.gender === 'male' ? 'OB' : 'OG')
                    : member.grade + '年';

                const alreadyRenewed = member.already_renewed;

                return `
                    <div class="card mb-3 ${alreadyRenewed ? 'border-secondary' : ''}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-1">${escapeHtml(member.name_kanji)} <small class="text-muted">(${escapeHtml(member.name_kana)})</small></h5>
                                    <p class="mb-0 text-muted">
                                        ${gradeDisplay}
                                        ${escapeHtml(member.faculty)}
                                        ${escapeHtml(member.department)}
                                    </p>
                                    <small class="text-muted">${member.academic_year}年度データ</small>
                                    ${alreadyRenewed ? '<br><span class="badge bg-secondary">登録済み</span>' : ''}
                                </div>
                                <div class="col-md-4 text-end">
                                    ${alreadyRenewed
                                        ? '<span class="text-muted">既に登録済みです</span>'
                                        : `<a href="/renew/confirm?member_id=${member.id}" class="btn btn-success">
                                            選択して次へ <i class="bi bi-arrow-right"></i>
                                           </a>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        searchResults.style.display = 'block';
    }

    // HTMLエスケープ
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
