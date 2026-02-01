<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF解析結果の確認 - 合宿費用計算</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .data-row {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .data-row:last-child {
            border-bottom: none;
        }
        .data-row .label {
            font-weight: 500;
            color: #495057;
        }
        .data-row .value {
            font-size: 1.1em;
            color: #212529;
        }
        .data-row.null-value .value {
            color: #6c757d;
            font-style: italic;
        }
        .edit-input {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <?php require VIEWS_PATH . '/layouts/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="d-flex align-items-center mb-4">
                    <a href="/pdf/upload" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i> 戻る
                    </a>
                    <h2 class="mb-0">PDF解析結果の確認</h2>
                </div>

                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <strong>PDFの解析が完了しました</strong>
                    <p class="mb-0 mt-2">以下の内容を確認し、必要に応じて修正してから「合宿に反映」ボタンを押してください。</p>
                </div>

                <?php if (!empty($parsedData['errors'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>エラーが見つかりました</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($parsedData['errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- 解析結果 -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-pdf"></i>
                            <?= htmlspecialchars($originalName) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="parsedDataContainer">
                            <!-- JavaScriptで動的に生成 -->
                        </div>
                    </div>
                </div>

                <!-- アクションボタン -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-primary btn-lg" id="applyBtn">
                        <i class="bi bi-check-lg"></i> 合宿に反映
                    </button>
                    <a href="/pdf/cancel" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-x-lg"></i> キャンセル
                    </a>
                </div>

                <!-- プログレス表示 -->
                <div id="saveProgress" class="mt-3" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                            保存中...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const parsedData = <?= json_encode($parsedData) ?>;
        const campId = new URLSearchParams(window.location.search).get('camp_id') || sessionStorage.getItem('campId');

        // フィールド定義（日本語ラベル）
        const fieldLabels = {
            'lodging_fee_per_night': '1泊あたり宿泊費（3食込み）',
            'hot_spring_tax': '入湯税（1泊あたり）',
            'court_fee_per_unit': 'テニスコート料金（半日1面）',
            'gym_fee_per_unit': '体育館料金（1コマ）',
            'banquet_fee_per_person': '宴会場料金（1人あたり）',
            'bus_fee_round_trip': 'バス料金（往復）',
            'bus_fee_outbound': 'バス料金（往路）',
            'bus_fee_return': 'バス料金（復路）',
            'highway_fee_outbound': '高速代（往路）',
            'highway_fee_return': '高速代（復路）',
            'facility_name': '施設名',
            'total_amount': '合計金額',
            'participant_count': '参加人数',
        };

        // 解析結果を表示
        function renderParsedData() {
            const container = document.getElementById('parsedDataContainer');
            container.innerHTML = '';

            for (const [key, value] of Object.entries(parsedData)) {
                if (key === 'type' || key === 'dates') continue;
                if (!fieldLabels[key]) continue;

                const row = document.createElement('div');
                row.className = 'data-row row align-items-center';
                if (value === null) {
                    row.classList.add('null-value');
                }

                const label = document.createElement('div');
                label.className = 'col-md-4 label';
                label.textContent = fieldLabels[key];

                const valueCol = document.createElement('div');
                valueCol.className = 'col-md-8';

                if (typeof value === 'number' || value === null) {
                    // 数値フィールドは編集可能
                    const inputGroup = document.createElement('div');
                    inputGroup.className = 'input-group edit-input';

                    const input = document.createElement('input');
                    input.type = 'number';
                    input.className = 'form-control';
                    input.value = value || '';
                    input.dataset.field = key;
                    input.placeholder = value === null ? '(未抽出)' : '';

                    const addon = document.createElement('span');
                    addon.className = 'input-group-text';
                    addon.textContent = '円';

                    inputGroup.appendChild(input);
                    inputGroup.appendChild(addon);
                    valueCol.appendChild(inputGroup);
                } else {
                    // 文字列フィールドは表示のみ
                    const span = document.createElement('span');
                    span.className = 'value';
                    span.textContent = value || '(未抽出)';
                    valueCol.appendChild(span);
                }

                row.appendChild(label);
                row.appendChild(valueCol);
                container.appendChild(row);
            }
        }

        // 合宿に反映
        document.getElementById('applyBtn').addEventListener('click', async () => {
            const inputs = document.querySelectorAll('.edit-input input');
            const data = {};

            inputs.forEach(input => {
                const field = input.dataset.field;
                const value = input.value ? parseInt(input.value) : null;
                data[field] = value;
            });

            const applyBtn = document.getElementById('applyBtn');
            const saveProgress = document.getElementById('saveProgress');

            applyBtn.disabled = true;
            saveProgress.style.display = 'block';

            try {
                const response = await fetch('/pdf/apply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        camp_id: parseInt(campId),
                        data: data,
                    }),
                });

                const result = await response.json();

                if (result.success) {
                    alert('合宿データに反映しました');
                    window.location.href = result.redirect;
                } else {
                    throw new Error(result.error || '保存に失敗しました');
                }
            } catch (error) {
                alert('エラー: ' + error.message);
                applyBtn.disabled = false;
                saveProgress.style.display = 'none';
            }
        });

        // 初期表示
        renderParsedData();
    </script>
</body>
</html>
