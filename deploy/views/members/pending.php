<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>入会申請一覧</h1>
        <p class="text-muted mb-0">承認待ちの入会申請を確認できます</p>
    </div>
    <a href="/members" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> 会員一覧に戻る
    </a>
</div>

<!-- 承認待ち件数 -->
<div class="card mb-4 border-warning">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-3 me-3"></i>
            <div>
                <h5 class="mb-1">承認待ち: <span id="pendingCount">0</span>件</h5>
                <p class="text-muted mb-0 small">入会申請を確認して、承認または却下してください</p>
            </div>
        </div>
    </div>
</div>

<!-- 入会申請一覧 -->
<div id="pendingList">
    <div class="text-center py-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">読み込み中...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadPendingMembers();
});

async function loadPendingMembers() {
    try {
        const res = await fetch('/index.php?route=api/members/pending');
        const data = await res.json();

        if (data.success) {
            renderPendingMembers(data.data.members);
            document.getElementById('pendingCount').textContent = data.data.count;
        }
    } catch (err) {
        console.error(err);
        document.getElementById('pendingList').innerHTML = `
            <div class="alert alert-danger">
                データの取得に失敗しました
            </div>
        `;
    }
}

function renderPendingMembers(members) {
    const container = document.getElementById('pendingList');

    if (members.length === 0) {
        container.innerHTML = `
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-check-circle fs-1 mb-3 d-block"></i>
                    <p class="mb-0">現在、承認待ちの入会申請はありません</p>
                </div>
            </div>
        `;
        return;
    }

    container.innerHTML = members.map(m => `
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title mb-2">${escapeHtml(m.name_kanji)} <small class="text-muted">(${escapeHtml(m.name_kana)})</small></h5>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <small class="text-muted d-block">学籍番号</small>
                                <code>${escapeHtml(m.student_id)}</code>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">学部・学科</small>
                                ${escapeHtml(m.faculty)} ${escapeHtml(m.department)}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">学年・性別</small>
                                ${formatGradeDisplay(m.grade, m.gender, m.enrollment_year)} / ${m.gender === 'male' ? '男性' : '女性'}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">申請日</small>
                                ${formatDate(m.created_at)}
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-info" onclick="showDetail(${m.id})">
                                <i class="bi bi-info-circle"></i> 詳細
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-column justify-content-center">
                        <button class="btn btn-success mb-2" onclick="approveMember(${m.id}, '${escapeHtml(m.name_kanji).replace(/'/g, "\\'")}')">
                            <i class="bi bi-check-circle"></i> 承認
                        </button>
                        <button class="btn btn-outline-danger" onclick="rejectMember(${m.id}, '${escapeHtml(m.name_kanji).replace(/'/g, "\\'")}')">
                            <i class="bi bi-x-circle"></i> 却下
                        </button>
                    </div>
                </div>

                <!-- 詳細情報（折りたたみ） -->
                <div class="collapse mt-3 pt-3 border-top" id="detail-${m.id}">
                    <div class="row g-2 small">
                        <div class="col-md-4">
                            <strong>電話番号:</strong> ${escapeHtml(m.phone)}
                        </div>
                        <div class="col-md-4">
                            <strong>緊急連絡先:</strong> ${escapeHtml(m.emergency_contact)}
                        </div>
                        <div class="col-md-4">
                            <strong>生年月日:</strong> ${escapeHtml(m.birthdate)}
                        </div>
                        <div class="col-md-6">
                            <strong>住所:</strong> ${escapeHtml(m.address)}
                        </div>
                        <div class="col-md-6">
                            <strong>メールアドレス:</strong> ${escapeHtml(m.email || '-')}
                        </div>
                        <div class="col-md-4">
                            <strong>LINE名:</strong> ${escapeHtml(m.line_name)}
                        </div>
                        <div class="col-md-4">
                            <strong>アレルギー:</strong> ${escapeHtml(m.allergy || 'なし')}
                        </div>
                        <div class="col-md-4">
                            <strong>SNS投稿:</strong> ${m.sns_allowed == 1 ? '可' : '不可'}
                        </div>
                        <div class="col-md-6">
                            <strong>コート予約番号:</strong> ${escapeHtml(m.sports_registration_no || '-')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function showDetail(id) {
    const detailEl = document.getElementById(`detail-${id}`);
    const collapse = new bootstrap.Collapse(detailEl, { toggle: true });
}

async function approveMember(id, name) {
    if (!confirm(`「${name}」さんの入会申請を承認しますか？`)) return;

    try {
        const res = await fetch(`/index.php?route=api/members/${id}/approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        const result = await res.json();

        if (result.success) {
            showToast('承認しました');
            loadPendingMembers();
        } else {
            alert(result.error?.message || '承認に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function rejectMember(id, name) {
    if (!confirm(`「${name}」さんの入会申請を却下しますか？\n\n※この操作により、申請データは削除されます。`)) return;

    try {
        const res = await fetch(`/index.php?route=api/members/${id}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        const result = await res.json();

        if (result.success) {
            showToast('却下しました');
            loadPendingMembers();
        } else {
            alert(result.error?.message || '却下に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function formatGradeDisplay(grade, gender, enrollmentYear) {
    if (grade === 'OB' || grade === 'OG') {
        return grade;
    }

    if (grade === '3') {
        let retired = false;
        if (enrollmentYear) {
            const retirementDate = new Date(parseInt(enrollmentYear) + 2, 9, 1);
            retired = new Date() >= retirementDate;
        } else {
            const month = new Date().getMonth();
            retired = month >= 9 || month <= 2;
        }
        if (retired) return gender === 'male' ? 'OB' : 'OG';
    }

    return `${grade}年`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const day = ('0' + date.getDate()).slice(-2);
    const hour = ('0' + date.getHours()).slice(-2);
    const min = ('0' + date.getMinutes()).slice(-2);
    return `${year}/${month}/${day} ${hour}:${min}`;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
