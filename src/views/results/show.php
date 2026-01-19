<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/camps/' . $campId) ?>" class="text-decoration-none">&larr; 戻る</a>
        <h1 class="mt-2"><?= htmlspecialchars($camp['name']) ?> - 計算結果</h1>
    </div>
    <div>
        <a href="<?= url('/camps/' . $campId . '/partial-schedule') ?>" class="btn btn-outline-secondary me-2">途参途抜一覧</a>
        <a href="<?= url('/api/camps/' . $campId . '/export/excel') ?>" class="btn btn-outline-success me-2">Excel出力</a>
        <a href="<?= url('/api/camps/' . $campId . '/export/pdf') ?>" target="_blank" class="btn btn-outline-primary">PDF出力</a>
    </div>
</div>

<div id="resultContainer">
    <div class="text-center py-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">計算中...</span>
        </div>
    </div>
</div>

<script>
const campId = <?= $campId ?>;

document.addEventListener('DOMContentLoaded', loadResult);

async function loadResult() {
    try {
        const res = await fetch(`/index.php?route=api/camps/${campId}/calculate`);
        const data = await res.json();

        if (data.success) {
            renderResult(data.data);
        } else {
            document.getElementById('resultContainer').innerHTML = `
                <div class="alert alert-danger">${data.error?.message || '計算に失敗しました'}</div>
            `;
        }
    } catch (err) {
        console.error(err);
        document.getElementById('resultContainer').innerHTML = `
            <div class="alert alert-danger">通信エラーが発生しました</div>
        `;
    }
}

function renderResult(result) {
    const { camp, participants, summary } = result;

    let html = `
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">集計サマリー</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3>¥${Number(summary.total_amount).toLocaleString()}</h3>
                        <p class="text-muted mb-0">総費用</p>
                    </div>
                    <div class="col-md-4">
                        <h3>${summary.participant_count}名</h3>
                        <p class="text-muted mb-0">参加者数</p>
                    </div>
                    <div class="col-md-4">
                        <h3>¥${Number(summary.average_amount).toLocaleString()}</h3>
                        <p class="text-muted mb-0">平均負担額</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">参加者別精算金額</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>名前</th>
                                <th class="text-end">負担額</th>
                                <th>内訳</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    for (const p of participants) {
        const items = p.items.map(item => {
            return `${item.name}: ¥${Number(item.amount).toLocaleString()}`;
        });

        html += `
            <tr>
                <td class="fw-bold">${escapeHtml(p.name)}</td>
                <td class="text-end">
                    <span class="badge bg-primary fs-6">¥${Number(p.total).toLocaleString()}</span>
                </td>
                <td>
                    <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#detail-${p.participant_id}">
                        詳細を表示
                    </button>
                    <div class="collapse mt-2" id="detail-${p.participant_id}">
                        <ul class="list-group list-group-flush small">
                            ${p.items.map(item => `
                                <li class="list-group-item d-flex justify-content-between py-1">
                                    <span>${escapeHtml(item.name)}</span>
                                    <span class="${item.amount < 0 ? 'text-danger' : ''}">¥${Number(item.amount).toLocaleString()}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                </td>
            </tr>
        `;
    }

    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    document.getElementById('resultContainer').innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
