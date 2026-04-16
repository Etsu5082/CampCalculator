<?php
$pageTitle = htmlspecialchars($camp['name']) . ' - 申し込み';
$appName = '合宿申し込み';

ob_start();
?>

<div class="card shadow">
    <div class="card-body p-4">
        <h2 class="text-center mb-2"><?= htmlspecialchars($camp['name']) ?></h2>
        <p class="text-center text-muted mb-4">
            <?= date('Y年m月d日', strtotime($camp['start_date'])) ?> 〜
            <?= date('Y年m月d日', strtotime($camp['end_date'])) ?>
            （<?= $camp['nights'] ?>泊<?= $camp['nights'] + 1 ?>日）
        </p>

        <?php if (!empty($tokenData['deadline'])): ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-clock"></i> 申し込み締切: <?= date('Y年m月d日 H:i', strtotime($tokenData['deadline'])) ?>
        </div>
        <?php endif; ?>

        <hr class="my-4">

        <div class="mb-4">
            <h5 class="mb-3">名前を入力して、自分を検索してください</h5>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="searchInput" placeholder="名前（漢字またはカナ）">
                <button class="btn btn-primary" type="button" onclick="searchMembers()">
                    <i class="bi bi-search"></i> 検索
                </button>
            </div>
        </div>

        <div id="searchResults"></div>

        <div id="noResults" class="alert alert-warning d-none">
            <i class="bi bi-exclamation-triangle"></i>
            名前が見つかりませんでした。幹事に連絡してください。<br>
            <small class="text-muted">名簿に登録されていない方は申し込みできません。</small>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="button" class="btn btn-primary btn-lg" id="nextButton" disabled onclick="goToConfirm()">
                選択して次へ <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<script>
let selectedMemberId = null;

async function searchMembers() {
    const searchQuery = document.getElementById('searchInput').value.trim();
    const resultsDiv = document.getElementById('searchResults');
    const noResultsDiv = document.getElementById('noResults');
    const nextButton = document.getElementById('nextButton');

    if (!searchQuery) {
        resultsDiv.innerHTML = '';
        noResultsDiv.classList.add('d-none');
        return;
    }

    try {
        const response = await fetch(`/apply/<?= $token ?>/search?search=${encodeURIComponent(searchQuery)}`);
        const data = await response.json();

        if (data.success && data.data.members.length > 0) {
            displayResults(data.data.members);
            noResultsDiv.classList.add('d-none');
        } else {
            resultsDiv.innerHTML = '';
            noResultsDiv.classList.remove('d-none');
            selectedMemberId = null;
            nextButton.disabled = true;
        }
    } catch (error) {
        console.error('Search error:', error);
        alert('検索中にエラーが発生しました');
    }
}

function displayResults(members) {
    const resultsDiv = document.getElementById('searchResults');

    const html = `
        <div class="list-group">
            <div class="list-group-item bg-light"><strong>検索結果</strong></div>
            ${members.map(member => `
                <label class="list-group-item list-group-item-action cursor-pointer">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="member" value="${member.id}"
                               onchange="selectMember(${member.id})">
                        <div class="form-check-label w-100">
                            <strong>${escapeHtml(member.name_kanji)}</strong> （${escapeHtml(member.name_kana)}）
                            <br>
                            <small class="text-muted">
                                ${member.grade}年 ${escapeHtml(member.faculty)} ${escapeHtml(member.department)}
                            </small>
                        </div>
                    </div>
                </label>
            `).join('')}
        </div>
    `;

    resultsDiv.innerHTML = html;
}

function selectMember(memberId) {
    selectedMemberId = memberId;
    document.getElementById('nextButton').disabled = false;
}

function goToConfirm() {
    if (selectedMemberId) {
        window.location.href = `/apply/<?= $token ?>/confirm?member_id=${selectedMemberId}`;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Enterキーで検索
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchMembers();
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/public.php';
?>
