<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>合宿一覧</h1>
    <button class="btn btn-primary" onclick="showCreateModal()">
        + 新規合宿作成
    </button>
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
