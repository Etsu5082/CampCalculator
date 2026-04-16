<style>
.dashboard-card {
    transition: transform 0.15s, box-shadow 0.15s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}
.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    color: inherit;
}
.dashboard-icon {
    font-size: 2.5rem;
    opacity: 0.85;
}
</style>

<div class="mb-4">
    <h1 class="mb-1">ダッシュボード</h1>
    <p class="text-muted mb-0">サークル管理システム</p>
</div>

<!-- メイン機能 -->
<h6 class="text-uppercase text-muted fw-bold mb-3 small">管理機能</h6>
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <a href="/camps" class="dashboard-card card shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-primary"><i class="bi bi-house-gear"></i></div>
                <div>
                    <h5 class="mb-1">合宿管理</h5>
                    <small class="text-muted">合宿の作成・費用計算・参加者管理</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="/members" class="dashboard-card card shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-success"><i class="bi bi-people"></i></div>
                <div>
                    <h5 class="mb-1">会員名簿</h5>
                    <small class="text-muted">会員情報の管理・検索・編集</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="/academic-years" class="dashboard-card card shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-warning"><i class="bi bi-calendar3"></i></div>
                <div>
                    <h5 class="mb-1">年度管理</h5>
                    <small class="text-muted">年度の作成・入会受付の開閉</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="/members/pending" class="dashboard-card card shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-danger"><i class="bi bi-person-check"></i></div>
                <div>
                    <h5 class="mb-1">入会承認</h5>
                    <small class="text-muted">新規・継続入会の申請を承認</small>
                    <span id="dashPendingBadge" class="badge bg-danger ms-1" style="display:none"></span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="/pdf/upload" class="dashboard-card card shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-secondary"><i class="bi bi-file-pdf"></i></div>
                <div>
                    <h5 class="mb-1">PDF読み取り</h5>
                    <small class="text-muted">PDFから参加者データを取り込む</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="/guide" class="dashboard-card card shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-info"><i class="bi bi-question-circle"></i></div>
                <div>
                    <h5 class="mb-1">使い方ガイド</h5>
                    <small class="text-muted">各機能の操作説明</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- 公開フォーム -->
<h6 class="text-uppercase text-muted fw-bold mb-3 small">公開フォーム（URL共有用）</h6>
<div class="row g-3">
    <div class="col-md-4 col-sm-6">
        <a href="/enroll" class="dashboard-card card shadow-sm h-100 text-decoration-none" target="_blank">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-success"><i class="bi bi-person-plus"></i></div>
                <div>
                    <h5 class="mb-1">新規入会フォーム</h5>
                    <small class="text-muted">新1・2年生向け入会申請</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="/renew" class="dashboard-card card shadow-sm h-100 text-decoration-none" target="_blank">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-primary"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <h5 class="mb-1">継続入会フォーム</h5>
                    <small class="text-muted">2年生以上の継続登録</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="/portal" class="dashboard-card card shadow-sm h-100 text-decoration-none" target="_blank">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="dashboard-icon text-warning"><i class="bi bi-grid"></i></div>
                <div>
                    <h5 class="mb-1">会員ポータル</h5>
                    <small class="text-muted">部員向け公開ページ</small>
                </div>
            </div>
        </a>
    </div>
</div>

<script>
// 承認待ち件数を取得してバッジ表示
(async () => {
    try {
        const res = await fetch('/index.php?route=api/members/pending/count');
        const data = await res.json();
        const count = data.data?.count ?? 0;
        if (count > 0) {
            const badge = document.getElementById('dashPendingBadge');
            badge.textContent = count + '件';
            badge.style.display = 'inline';
        }
    } catch (e) {}
})();
</script>
