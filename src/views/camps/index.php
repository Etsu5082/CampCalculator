<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>合宿一覧</h1>
    <button class="btn btn-primary" onclick="showCreateModal()">
        + 新規合宿作成
    </button>
</div>

<!-- 使い方ガイド -->
<div class="card mb-4 border-info">
    <div class="card-header bg-info bg-opacity-10 border-info">
        <a class="d-flex justify-content-between align-items-center text-decoration-none text-dark"
           data-bs-toggle="collapse" href="#usageGuide" role="button" aria-expanded="false">
            <span><strong>使い方ガイド</strong> - 初めての方はこちら</span>
            <i class="bi bi-chevron-down"></i>
        </a>
    </div>
    <div class="collapse" id="usageGuide">
        <div class="card-body">
            <h5 class="card-title mb-3">合宿費用計算アプリの使い方</h5>

            <div class="accordion" id="guideAccordion">
                <!-- Step 1 -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                            <span class="badge bg-primary me-2">1</span> 合宿を作成する
                        </button>
                    </h2>
                    <div id="step1" class="accordion-collapse collapse show" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>「+ 新規合宿作成」ボタンをクリック</li>
                                <li>基本情報（合宿名、日程、泊数）を入力</li>
                                <li>宿泊費用、施設利用料、食事単価、交通費を設定</li>
                                <li>「保存」をクリック</li>
                            </ol>
                            <div class="alert alert-info mb-0">
                                <small><strong>ポイント:</strong> 過去の合宿を「複製」して、日程と名前を変更するだけで簡単に新しい合宿を作成できます。</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                            <span class="badge bg-primary me-2">2</span> 参加者を登録する
                        </button>
                    </h2>
                    <div id="step2" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <p>合宿詳細画面の「参加者管理」タブで参加者を登録します。</p>
                            <h6>CSV一括登録（おすすめ）</h6>
                            <ol>
                                <li>「CSV一括登録」ボタンをクリック</li>
                                <li>以下の形式でテキストを入力またはペースト：
                                    <pre class="bg-light p-2 mt-2 mb-2"><code>山田太郎,1男
佐藤花子,2女
鈴木一郎,OB
高橋美咲,OG</code></pre>
                                </li>
                                <li>「登録」をクリック</li>
                            </ol>
                            <div class="alert alert-warning mb-0">
                                <small><strong>形式:</strong> 1列目=氏名、2列目=学年性別（1男、2女、3男、4女、OB、OGなど）<br>
                                全角数字（１男、２女）や「1年男」形式にも対応しています。</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                            <span class="badge bg-primary me-2">3</span> 途中参加・途中抜けを設定する
                        </button>
                    </h2>
                    <div id="step3" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <p>デフォルトでは全員が「1日目往路バスから最終日復路バスまで」のフル参加として登録されます。</p>
                            <ol>
                                <li>途中参加・途中抜けの人の「編集」ボタンをクリック</li>
                                <li>「参加開始」「離脱」のタイミングを変更</li>
                                <li>バス利用の有無を設定</li>
                                <li>「保存」をクリック</li>
                            </ol>
                            <div class="alert alert-info mb-0">
                                <small><strong>自動計算:</strong> 参加タイミングに応じて、宿泊数・食事の追加/欠食が自動計算されます。</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                            <span class="badge bg-primary me-2">4</span> 日程を設定する（オプション）
                        </button>
                    </h2>
                    <div id="step4" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <p>「日程設定」タブで各日の活動内容を設定できます。</p>
                            <ul>
                                <li><strong>午前・午後:</strong> テニスコート / 体育館 / なし を選択</li>
                                <li><strong>コート面数:</strong> 使用する面数を入力（料金計算に使用）</li>
                                <li><strong>宴会:</strong> 宴会場を使用する日を設定</li>
                            </ul>
                            <div class="alert alert-secondary mb-0">
                                <small>日程設定は任意です。設定しない場合、施設利用料は0円として計算されます。</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step5">
                            <span class="badge bg-primary me-2">5</span> 雑費を登録する（オプション）
                        </button>
                    </h2>
                    <div id="step5" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <p>「雑費管理」タブで、その他の費用を登録できます。</p>
                            <ul>
                                <li>タイムテーブル上のセルをクリックして雑費を追加</li>
                                <li>項目名と金額を入力</li>
                                <li><strong>割り勘対象:</strong> そのタイミングに参加していた人で自動的に割り勘</li>
                            </ul>
                            <div class="alert alert-info mb-0">
                                <small><strong>例:</strong> 2日目夜の飲み物代を登録すると、2日目夜に参加していた人だけで割り勘されます。</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 6 -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step6">
                            <span class="badge bg-primary me-2">6</span> 計算結果を確認・出力する
                        </button>
                    </h2>
                    <div id="step6" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>「計算結果を見る」ボタンをクリック</li>
                                <li>各参加者の負担額と内訳を確認</li>
                                <li>「途参途抜一覧」で途中参加者のスケジュールを確認</li>
                                <li>「PDF出力」または「Excel出力」でファイルを保存</li>
                            </ol>
                            <div class="alert alert-success mb-0">
                                <small><strong>出力形式:</strong><br>
                                - フル参加者は1行にまとめて表示<br>
                                - 途中参加・途中抜けは個別に詳細表示<br>
                                - 途参途抜一覧で各スロットの参加状況を○×で表示</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h6>よくある質問</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Q: 食事の追加/欠食はどう計算される？</strong></p>
                    <p class="text-muted small">A: 1泊に含まれる食事は「その日の夕食、翌日の朝食・昼食」です。参加タイミングにより自動で追加/欠食が計算されます。</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Q: 端数はどうなる？</strong></p>
                    <p class="text-muted small">A: 割り勘計算は四捨五入されます。合計の端数は会計担当が調整してください。</p>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="/index.php?route=guide" class="btn btn-outline-info">
                    詳細な使い方ガイドを見る →
                </a>
            </div>
        </div>
    </div>
</div>

<div id="campList" class="row">
    <div class="col-12 text-center py-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">読み込み中...</span>
        </div>
    </div>
</div>

<!-- 合宿作成/編集モーダル -->
<div class="modal fade" id="campModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="campModalTitle">新規合宿作成</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="campForm">
                    <input type="hidden" id="campId">

                    <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">合宿名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="campName" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">泊数 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="campNights" value="3" min="1" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">開始日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="campStartDate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">終了日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="campEndDate" required>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">宿泊費用</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">1泊料金（3食付）</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="lodgingFee" value="8000">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">保険料</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="insuranceFee" value="500">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">1日目昼食</label>
                            <select class="form-select" id="firstDayLunch">
                                <option value="0">対象外（各自調達）</option>
                                <option value="1">対象</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">施設利用料</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">コート1面あたり料金</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="courtFeePerUnit" value="5000" placeholder="1面1コマあたり">
                                <span class="input-group-text">円/面</span>
                            </div>
                            <small class="text-muted">日程設定で各コマの使用面数を指定できます</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">体育館1コマあたり料金</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="gymFeePerUnit" value="0" placeholder="1コマあたり">
                                <span class="input-group-text">円/コマ</span>
                            </div>
                            <small class="text-muted">日程設定で体育館を選択した場合に適用</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">宴会場料金（1人あたり）</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="banquetFeePerPerson" value="0" placeholder="1人あたり">
                                <span class="input-group-text">円/人</span>
                            </div>
                            <small class="text-muted">日程設定で宴会場を選択した場合に適用</small>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">食事単価（追加/欠食）</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">朝食</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">+</span>
                                        <input type="number" class="form-control" id="breakfastAdd" value="600">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">-</span>
                                        <input type="number" class="form-control" id="breakfastRemove" value="400">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">昼食</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">+</span>
                                        <input type="number" class="form-control" id="lunchAdd" value="990">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">-</span>
                                        <input type="number" class="form-control" id="lunchRemove" value="440">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">夕食</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">+</span>
                                        <input type="number" class="form-control" id="dinnerAdd" value="1200">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">-</span>
                                        <input type="number" class="form-control" id="dinnerRemove" value="800">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">交通費（総額）</h6>

                    <!-- バス料金 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">バス代</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="busRoundTrip" value="160000" placeholder="往復料金">
                                <span class="input-group-text">円（往復）</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="busSeparate" onchange="toggleBusSeparate()">
                                <label class="form-check-label" for="busSeparate">往路・復路を別々に設定</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3" id="busSeparateInputs" style="display:none;">
                        <div class="col-md-6">
                            <label class="form-label">往路バス代</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="busOutbound" value="80000">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">復路バス代</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="busReturn" value="80000">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                    </div>

                    <!-- バス高速代 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">往路高速代（バス）</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="highwayOutbound" value="15000">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">復路高速代（バス）</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="highwayReturn" value="15000">
                                <span class="input-group-text">円</span>
                            </div>
                        </div>
                    </div>

                    <!-- レンタカーオプション -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">レンタカー（オプション）</h6>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="useRentalCar" onchange="toggleRentalCar()">
                                <label class="form-check-label" for="useRentalCar">レンタカーを追加する（バス定員オーバー時など）</label>
                            </div>
                        </div>
                    </div>
                    <div id="rentalCarInputs" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">レンタカー代（総額）</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="rentalCarFee" value="0">
                                    <span class="input-group-text">円</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">レンタカー高速代（総額）</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="rentalCarHighwayFee" value="0">
                                    <span class="input-group-text">円</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">レンタカー定員</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="rentalCarCapacity" value="5">
                                    <span class="input-group-text">人</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="saveCamp()">保存</button>
            </div>
        </div>
    </div>
</div>

<script>
let campModal;

document.addEventListener('DOMContentLoaded', () => {
    campModal = new bootstrap.Modal(document.getElementById('campModal'));
    loadCamps();
});

async function loadCamps() {
    try {
        const res = await fetch('/index.php?route=api/camps');
        const data = await res.json();

        if (data.success) {
            renderCamps(data.data);
        }
    } catch (err) {
        console.error(err);
    }
}

function renderCamps(camps) {
    const container = document.getElementById('campList');

    if (camps.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5 text-muted">
                <p>まだ合宿がありません</p>
                <button class="btn btn-primary" onclick="showCreateModal()">最初の合宿を作成</button>
            </div>
        `;
        return;
    }

    container.innerHTML = camps.map(camp => `
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">${escapeHtml(camp.name)}</h5>
                        <p class="card-text text-muted mb-0">
                            ${camp.start_date} ～ ${camp.end_date}（${camp.nights}泊${parseInt(camp.nights) + 1}日）
                            <span class="badge bg-secondary ms-2">${camp.participant_count}名</span>
                        </p>
                    </div>
                    <div>
                        <button class="btn btn-outline-danger btn-sm me-2" onclick="deleteCamp(${camp.id}, '${escapeHtml(camp.name).replace(/'/g, "\\'")}')">削除</button>
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="duplicateCamp(${camp.id})">複製</button>
                        <a href="/index.php?route=camps/${camp.id}" class="btn btn-primary btn-sm">詳細</a>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function showCreateModal() {
    document.getElementById('campModalTitle').textContent = '新規合宿作成';
    document.getElementById('campId').value = '';
    document.getElementById('campForm').reset();
    campModal.show();
}

function toggleBusSeparate() {
    const isSeparate = document.getElementById('busSeparate').checked;
    document.getElementById('busSeparateInputs').style.display = isSeparate ? 'flex' : 'none';
}

function toggleRentalCar() {
    const useRentalCar = document.getElementById('useRentalCar').checked;
    document.getElementById('rentalCarInputs').style.display = useRentalCar ? 'block' : 'none';
}

async function saveCamp() {
    const id = document.getElementById('campId').value;
    const busSeparate = document.getElementById('busSeparate').checked;
    const useRentalCar = document.getElementById('useRentalCar').checked;

    const data = {
        name: document.getElementById('campName').value,
        start_date: document.getElementById('campStartDate').value,
        end_date: document.getElementById('campEndDate').value,
        nights: parseInt(document.getElementById('campNights').value),
        lodging_fee_per_night: parseInt(document.getElementById('lodgingFee').value) || 0,
        insurance_fee: parseInt(document.getElementById('insuranceFee').value) || 0,
        court_fee_per_unit: parseInt(document.getElementById('courtFeePerUnit').value) || null,
        gym_fee_per_unit: parseInt(document.getElementById('gymFeePerUnit').value) || null,
        banquet_fee_per_person: parseInt(document.getElementById('banquetFeePerPerson').value) || null,
        first_day_lunch_included: parseInt(document.getElementById('firstDayLunch').value),
        breakfast_add_price: parseInt(document.getElementById('breakfastAdd').value) || 0,
        breakfast_remove_price: parseInt(document.getElementById('breakfastRemove').value) || 0,
        lunch_add_price: parseInt(document.getElementById('lunchAdd').value) || 0,
        lunch_remove_price: parseInt(document.getElementById('lunchRemove').value) || 0,
        dinner_add_price: parseInt(document.getElementById('dinnerAdd').value) || 0,
        dinner_remove_price: parseInt(document.getElementById('dinnerRemove').value) || 0,
        bus_fee_round_trip: busSeparate ? null : (parseInt(document.getElementById('busRoundTrip').value) || null),
        bus_fee_separate: busSeparate ? 1 : 0,
        bus_fee_outbound: busSeparate ? (parseInt(document.getElementById('busOutbound').value) || null) : null,
        bus_fee_return: busSeparate ? (parseInt(document.getElementById('busReturn').value) || null) : null,
        highway_fee_outbound: parseInt(document.getElementById('highwayOutbound').value) || null,
        highway_fee_return: parseInt(document.getElementById('highwayReturn').value) || null,
        use_rental_car: useRentalCar ? 1 : 0,
        rental_car_fee: useRentalCar ? (parseInt(document.getElementById('rentalCarFee').value) || null) : null,
        rental_car_highway_fee: useRentalCar ? (parseInt(document.getElementById('rentalCarHighwayFee').value) || null) : null,
        rental_car_capacity: useRentalCar ? (parseInt(document.getElementById('rentalCarCapacity').value) || null) : null,
    };

    const url = id ? `/index.php?route=api/camps/${id}` : '/index.php?route=api/camps';
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            campModal.hide();
            loadCamps();
            showToast('保存しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function duplicateCamp(id) {
    if (!confirm('この合宿を複製しますか？')) return;

    try {
        const res = await fetch(`/index.php?route=api/camps/${id}/duplicate`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();

        if (data.success) {
            loadCamps();
            showToast('合宿を複製しました');
        } else {
            alert(data.error?.message || '複製に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteCamp(id, name) {
    if (!confirm(`「${name}」を削除しますか？\n\n※参加者、日程、雑費など関連データもすべて削除されます。この操作は取り消せません。`)) return;

    try {
        const res = await fetch(`/index.php?route=api/camps/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();

        if (data.success) {
            loadCamps();
            showToast('合宿を削除しました');
        } else {
            alert(data.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
