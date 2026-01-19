<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/camps/' . $campId) ?>" class="text-decoration-none">&larr; 戻る</a>
        <h1 class="mt-2"><?= htmlspecialchars($camp['name']) ?> - 途参途抜一覧</h1>
    </div>
    <div>
        <a href="<?= url('/camps/' . $campId . '/result') ?>" class="btn btn-outline-primary me-2">計算結果</a>
    </div>
</div>

<div id="scheduleContainer">
    <div class="text-center py-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">読み込み中...</span>
        </div>
    </div>
</div>

<style>
.schedule-table {
    font-size: 0.85rem;
}
.schedule-table th, .schedule-table td {
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    padding: 0.3rem 0.5rem;
}
.schedule-table .day-header {
    background-color: #f8f9fa;
    font-weight: bold;
}
.schedule-table .participant-name {
    text-align: left;
    font-weight: bold;
    position: sticky;
    left: 0;
    background-color: white;
    z-index: 1;
}
.schedule-table .participant-desc {
    text-align: left;
    font-size: 0.75rem;
    color: #666;
    max-width: 300px;
    white-space: normal;
}
.schedule-table .attend {
    color: #198754;
    font-weight: bold;
}
.schedule-table .not-attend {
    color: #dc3545;
}
.schedule-table .total-row {
    background-color: #e9ecef;
    font-weight: bold;
}
.schedule-table thead th {
    position: sticky;
    top: 0;
    background-color: white;
    z-index: 2;
}
.table-scroll-container {
    max-height: 70vh;
    overflow: auto;
}
</style>

<script>
const campId = <?= $campId ?>;

document.addEventListener('DOMContentLoaded', loadSchedule);

async function loadSchedule() {
    try {
        const res = await fetch(`/index.php?route=api/camps/${campId}/partial-schedule`);
        const data = await res.json();

        if (data.success) {
            renderSchedule(data.data);
        } else {
            document.getElementById('scheduleContainer').innerHTML = `
                <div class="alert alert-danger">${data.error?.message || 'データ取得に失敗しました'}</div>
            `;
        }
    } catch (err) {
        console.error(err);
        document.getElementById('scheduleContainer').innerHTML = `
            <div class="alert alert-danger">通信エラーが発生しました</div>
        `;
    }
}

function renderSchedule(result) {
    const { camp, headers, rows, totals, partial_count, total_count } = result;

    if (rows.length === 0) {
        document.getElementById('scheduleContainer').innerHTML = `
            <div class="alert alert-info">
                途中参加・途中抜けの参加者はいません。<br>
                全員がフル参加（${camp.nights + 1}日間）です。
            </div>
        `;
        return;
    }

    let html = `
        <div class="alert alert-info mb-3">
            <strong>途中参加・途中抜け:</strong> ${partial_count}名 / 全${total_count}名
        </div>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-scroll-container">
                    <table class="table table-bordered schedule-table mb-0">
                        <thead>
    `;

    // 日付ヘッダー行
    html += '<tr><th rowspan="2" class="participant-name">氏名</th>';
    for (const dayHeader of headers) {
        const colCount = dayHeader.columns.length;
        html += `<th colspan="${colCount}" class="day-header">${dayHeader.day}日目</th>`;
    }
    html += '</tr>';

    // 項目ヘッダー行
    html += '<tr>';
    for (const dayHeader of headers) {
        for (const col of dayHeader.columns) {
            html += `<th>${col.label}</th>`;
        }
    }
    html += '</tr></thead><tbody>';

    // 参加者行
    for (const row of rows) {
        const gradeLabel = getGradeLabel(row.grade);
        const genderIcon = row.gender === 'male' ? '♂' : '♀';

        html += `<tr>
            <td class="participant-name">${escapeHtml(row.name)} <small class="text-muted">(${gradeLabel}${genderIcon})</small></td>`;

        for (const dayHeader of headers) {
            for (const col of dayHeader.columns) {
                const attends = row.schedule[col.key];
                if (attends) {
                    html += '<td class="attend">○</td>';
                } else {
                    html += '<td class="not-attend">×</td>';
                }
            }
        }
        html += '</tr>';
    }

    // 集計行
    html += '<tr class="total-row"><td>合計</td>';
    for (const dayHeader of headers) {
        for (const col of dayHeader.columns) {
            const count = totals[col.key] || 0;
            html += `<td>${count}</td>`;
        }
    }
    html += '</tr>';

    html += '</tbody></table></div></div></div>';

    document.getElementById('scheduleContainer').innerHTML = html;
}

function getGradeLabel(grade) {
    if (grade === 0) return 'OB';
    return grade + '年';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
