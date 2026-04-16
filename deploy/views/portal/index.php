<!-- ヘッダー -->
<div class="text-center mb-4 pt-3">
    <h2 class="fw-normal text-dark">Laissez-Faire T.C.</h2>
    <p class="text-muted mb-0">会員ポータル</p>
</div>

<?php if (!empty($activeCamps)): ?>
<!-- 募集中の合宿 -->
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning bg-opacity-25 border-warning">
        <i class="bi bi-megaphone"></i> 募集中の合宿
    </div>
    <div class="card-body">
        <?php foreach ($activeCamps as $camp): ?>
        <div class="d-flex justify-content-between align-items-center <?= $camp !== end($activeCamps) ? 'mb-3 pb-3 border-bottom' : '' ?>">
            <div>
                <h6 class="mb-1"><?= htmlspecialchars($camp['camp_name']) ?></h6>
                <small class="text-muted">
                    <?= date('Y/n/j', strtotime($camp['start_date'])) ?> 〜 <?= date('n/j', strtotime($camp['end_date'])) ?>
                    <?php if ($camp['deadline']): ?>
                    ・締切 <?= date('n/j', strtotime($camp['deadline'])) ?>
                    <?php endif; ?>
                </small>
            </div>
            <a href="/apply/<?= htmlspecialchars($camp['token']) ?>" class="btn btn-warning btn-sm">
                申し込む
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- メニュー -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <a href="/enroll" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-light p-2 me-3">
                        <i class="bi bi-person-plus text-secondary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark">新規入会</h6>
                        <small class="text-muted">新しく入会される方</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="/renew" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-light p-2 me-3">
                        <i class="bi bi-arrow-repeat text-secondary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark">継続入会</h6>
                        <small class="text-muted">昨年度から継続される方</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- 案内 -->
<div class="text-muted small text-center">
    <p class="mb-0">ご不明な点は幹事長までご連絡ください</p>
</div>
