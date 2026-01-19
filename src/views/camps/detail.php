<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/camps') ?>" class="text-decoration-none">&larr; 戻る</a>
        <h1 class="mt-2" id="campTitle"><?= htmlspecialchars($camp['name']) ?></h1>
    </div>
    <a href="<?= url('/camps/' . $campId . '/result') ?>" class="btn btn-success">計算結果を見る</a>
</div>

<!-- タブナビゲーション -->
<ul class="nav nav-tabs mb-4" id="campTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabBasic">基本情報</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSchedule">日程設定</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabParticipants">参加者管理</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabExpenses">雑費管理</button>
    </li>
</ul>

<div class="tab-content">
    <!-- 基本情報タブ -->
    <div class="tab-pane fade show active" id="tabBasic">
        <div id="basicInfo">読み込み中...</div>
    </div>

    <!-- 日程設定タブ -->
    <div class="tab-pane fade" id="tabSchedule">
        <div id="scheduleInfo">読み込み中...</div>
    </div>

    <!-- 参加者管理タブ -->
    <div class="tab-pane fade" id="tabParticipants">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>参加者一覧 <span class="badge bg-secondary" id="participantCount">0</span></h4>
            <div>
                <button class="btn btn-outline-danger btn-sm me-2" onclick="deleteAllParticipants()">全員削除</button>
                <button class="btn btn-outline-secondary btn-sm me-2" onclick="showCsvImportModal()">CSVインポート</button>
                <button class="btn btn-primary btn-sm" onclick="showParticipantModal()">+ 参加者を追加</button>
            </div>
        </div>

        <!-- 検索・フィルタ -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" id="participantSearch" placeholder="名前で検索..." oninput="filterParticipants()">
            </div>
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- 第1ソート -->
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text">並び替え</span>
                        <select class="form-select form-select-sm" id="sortPrimary" onchange="applySorting()" style="width: auto;">
                            <option value="name">名前</option>
                            <option value="grade" selected>学年</option>
                            <option value="gender">性別</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" id="sortPrimaryDir" onclick="toggleSortDirection('primary')" title="昇順/降順切り替え">↑</button>
                    </div>
                    <!-- 第2ソート -->
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text">→</span>
                        <select class="form-select form-select-sm" id="sortSecondary" onchange="applySorting()" style="width: auto;">
                            <option value="">なし</option>
                            <option value="name">名前</option>
                            <option value="grade">学年</option>
                            <option value="gender" selected>性別</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" id="sortSecondaryDir" onclick="toggleSortDirection('secondary')" title="昇順/降順切り替え">↑</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="participantList">読み込み中...</div>
    </div>

    <!-- 雑費管理タブ -->
    <div class="tab-pane fade" id="tabExpenses">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>雑費管理</h4>
            <button class="btn btn-primary btn-sm" onclick="showExpenseModal()">+ 全員対象の雑費を追加</button>
        </div>

        <!-- 時間割形式のUI -->
        <div class="card mb-4">
            <div class="card-header">
                <small class="text-muted">マスをクリックして、そのタイミングの参加者を対象とした雑費を追加できます</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="expenseScheduleTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:80px;"></th>
                                <!-- 日程は動的に生成 -->
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 時間割は動的に生成 -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 全員対象の雑費一覧 -->
        <h5 class="mt-4 mb-3">全員対象の雑費</h5>
        <div id="expenseListAll">読み込み中...</div>

        <!-- 建て替え総額集計 -->
        <h5 class="mt-4 mb-3">建て替え総額</h5>
        <div id="payerSummary">読み込み中...</div>
    </div>
</div>

<!-- 参加者編集モーダル -->
<div class="modal fade" id="participantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="participantModalTitle">参加者追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="participantForm">
                    <input type="hidden" id="participantId">

                    <div class="mb-3">
                        <label class="form-label">名前 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="participantName" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">学年</label>
                            <select class="form-select" id="participantGrade">
                                <option value="">未設定</option>
                                <option value="1">1年</option>
                                <option value="2">2年</option>
                                <option value="3">3年</option>
                                <option value="0">OB/OG</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">性別</label>
                            <select class="form-select" id="participantGender">
                                <option value="">未設定</option>
                                <option value="male">男</option>
                                <option value="female">女</option>
                            </select>
                            <small class="text-muted" id="obogHint" style="display:none;">OB/OGは性別で区別されます</small>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">参加期間</h6>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">参加開始</label>
                            <div class="input-group">
                                <select class="form-select" id="joinDay"></select>
                                <span class="input-group-text">日目</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">タイミング</label>
                            <select class="form-select" id="joinTiming" onchange="updateBusFromTiming()">
                                <!-- 選択肢はJavaScriptで動的に生成 -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">参加終了</label>
                            <div class="input-group">
                                <select class="form-select" id="leaveDay"></select>
                                <span class="input-group-text">日目</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">タイミング</label>
                            <select class="form-select" id="leaveTiming" onchange="updateBusFromTiming()">
                                <!-- 選択肢はJavaScriptで動的に生成 -->
                            </select>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">交通手段</h6>
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="useOutboundBus" checked>
                                <label class="form-check-label" for="useOutboundBus">往路バス</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="useReturnBus" checked>
                                <label class="form-check-label" for="useReturnBus">復路バス</label>
                            </div>
                        </div>
                        <div class="col-4" id="rentalCarOption" style="display:none;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="useRentalCar">
                                <label class="form-check-label" for="useRentalCar">レンタカー</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="deleteParticipantBtn" onclick="deleteParticipant()" style="display:none;">削除</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="saveParticipant()">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 雑費編集モーダル -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseModalTitle">雑費追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenseForm">
                    <input type="hidden" id="expenseId">

                    <div class="mb-3">
                        <label class="form-label">項目名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="expenseName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">金額（総額） <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="expenseAmount" required>
                            <span class="input-group-text">円</span>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">割り勘対象</h6>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="targetType" id="targetAll" value="all" checked onchange="toggleTargetSlot()">
                            <label class="form-check-label" for="targetAll">全参加者</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="targetType" id="targetSlot" value="slot" onchange="toggleTargetSlot()">
                            <label class="form-check-label" for="targetSlot">特定タイミングの参加者</label>
                        </div>
                    </div>

                    <div class="row mb-3" id="targetSlotInputs" style="display:none;">
                        <div class="col-6">
                            <select class="form-select" id="expenseTargetDay"></select>
                        </div>
                        <div class="col-6">
                            <select class="form-select" id="expenseTargetSlot">
                                <option value="morning">午前</option>
                                <option value="afternoon">午後</option>
                                <option value="banquet">宴会</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">建て替え者</h6>
                    <div class="mb-3">
                        <select class="form-select" id="expensePayerId">
                            <option value="">なし（未指定）</option>
                            <!-- 参加者リストは動的に生成 -->
                        </select>
                        <small class="text-muted">この費用を立て替えた人を選択</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="deleteExpenseBtn" onclick="deleteExpense()" style="display:none;">削除</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="saveExpense()">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- CSVインポートモーダル -->
<div class="modal fade" id="csvImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CSVインポート</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>CSV形式:</strong><br>
                    1列目: 氏名<br>
                    2列目: 学年性別（1男, 1女, 2男, 2女, 3男, 3女, OB, OG）<br>
                    <small class="text-muted">※ヘッダー行は不要です</small>
                </div>

                <!-- ファイルアップロード -->
                <div class="mb-3">
                    <label class="form-label">CSVファイルをアップロード</label>
                    <input type="file" class="form-control" id="csvFile" accept=".csv,.txt" onchange="loadCsvFile(this)">
                </div>

                <div class="text-center text-muted mb-3">または</div>

                <!-- テキスト貼り付け -->
                <div class="mb-3">
                    <label class="form-label">CSVデータを貼り付け</label>
                    <textarea class="form-control" id="csvData" rows="8" placeholder="田中太郎,1男&#10;鈴木花子,2女&#10;山田次郎,OB&#10;高橋美咲,OG"></textarea>
                </div>
                <div id="csvImportResult" class="d-none">
                    <div class="alert alert-success" id="csvSuccessMsg"></div>
                    <div class="alert alert-danger d-none" id="csvErrorMsg"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="importCsv()">インポート</button>
            </div>
        </div>
    </div>
</div>

<!-- 基本情報編集モーダル -->
<div class="modal fade" id="basicInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">基本情報を編集</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="basicInfoForm">
                    <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">合宿名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCampName" required>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">施設利用料</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">コート1面あたり料金</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editCourtFeePerUnit" min="0">
                                <span class="input-group-text">/面</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">体育館1コマあたり料金</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editGymFeePerUnit" min="0">
                                <span class="input-group-text">/コマ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">宴会場料金（1人あたり）</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editBanquetFeePerPerson" min="0">
                                <span class="input-group-text">/人</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">宿泊費用</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">1泊料金</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editLodgingFee" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">保険料</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editInsuranceFee" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">1日目昼食</label>
                            <select class="form-select" id="editFirstDayLunch">
                                <option value="1">対象</option>
                                <option value="0">対象外</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">食事単価</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">朝食追加</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editBreakfastAdd" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">昼食追加</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editLunchAdd" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">夕食追加</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editDinnerAdd" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">朝食削除</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editBreakfastRemove" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">昼食削除</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editLunchRemove" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">夕食削除</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editDinnerRemove" min="0">
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">交通費（バス）</h6>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editBusFeeSeparate" onchange="toggleEditBusFeeFields()">
                            <label class="form-check-label" for="editBusFeeSeparate">往路/復路を別々に設定</label>
                        </div>
                    </div>
                    <div class="row mb-3" id="editBusFeeRoundTripField">
                        <div class="col-md-6">
                            <label class="form-label">バス代（往復）</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editBusFeeRoundTrip" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3" id="editBusFeeSeparateFields" style="display:none;">
                        <div class="col-md-6">
                            <label class="form-label">往路バス代</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editBusFeeOutbound" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">復路バス代</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editBusFeeReturn" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">往路高速代</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editHighwayFeeOutbound" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">復路高速代</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="editHighwayFeeReturn" min="0">
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">レンタカー</h6>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editUseRentalCar" onchange="toggleEditRentalCarFields()">
                            <label class="form-check-label" for="editUseRentalCar">レンタカーを使用</label>
                        </div>
                    </div>
                    <div id="editRentalCarFields" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">レンタカー代</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control" id="editRentalCarFee" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">高速代</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control" id="editRentalCarHighwayFee" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">定員</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="editRentalCarCapacity" min="1">
                                    <span class="input-group-text">人</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="saveBasicInfo()">保存</button>
            </div>
        </div>
    </div>
</div>

<script>
const campId = <?= $campId ?>;
let campData = null;
let participantModal, expenseModal, csvImportModal, basicInfoModal;
// 並び替え設定
let sortConfig = {
    primary: { key: 'grade', direction: 1 },
    secondary: { key: 'gender', direction: 1 }
};

document.addEventListener('DOMContentLoaded', () => {
    participantModal = new bootstrap.Modal(document.getElementById('participantModal'));
    expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'));
    csvImportModal = new bootstrap.Modal(document.getElementById('csvImportModal'));
    basicInfoModal = new bootstrap.Modal(document.getElementById('basicInfoModal'));
    loadCampData();
});

async function loadCampData() {
    try {
        const res = await fetch(`/index.php?route=api/camps/${campId}`);
        const data = await res.json();

        if (data.success) {
            campData = data.data;
            renderBasicInfo();
            renderSchedule();
            renderParticipants();
            renderExpenses();
            setupDaySelectors();
        }
    } catch (err) {
        console.error(err);
    }
}

function renderBasicInfo() {
    const c = campData;
    document.getElementById('basicInfo').innerHTML = `
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-outline-primary btn-sm" onclick="showEditBasicInfoModal()">基本情報を編集</button>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>基本情報</span>
            </div>
            <div class="card-body">
                <p><strong>日程:</strong> ${c.start_date} ～ ${c.end_date}（${c.nights}泊${parseInt(c.nights)+1}日）</p>
                <div class="row">
                    <div class="col-md-4"><strong>コート単価:</strong> ¥${Number(c.court_fee_per_unit || 0).toLocaleString()}/面</div>
                    <div class="col-md-4"><strong>体育館単価:</strong> ¥${Number(c.gym_fee_per_unit || 0).toLocaleString()}/コマ</div>
                    <div class="col-md-4"><strong>宴会場単価:</strong> ¥${Number(c.banquet_fee_per_person || 0).toLocaleString()}/人</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">宿泊費用</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>1泊料金:</strong> ¥${Number(c.lodging_fee_per_night).toLocaleString()}</div>
                    <div class="col-md-4"><strong>保険料:</strong> ¥${Number(c.insurance_fee).toLocaleString()}</div>
                    <div class="col-md-4"><strong>1日目昼食:</strong> ${c.first_day_lunch_included ? '対象' : '対象外'}</div>
                </div>
                <hr>
                <p class="mb-1"><strong>食事単価:</strong></p>
                <div class="row">
                    <div class="col-md-4">朝食: +¥${c.breakfast_add_price} / -¥${c.breakfast_remove_price}</div>
                    <div class="col-md-4">昼食: +¥${c.lunch_add_price} / -¥${c.lunch_remove_price}</div>
                    <div class="col-md-4">夕食: +¥${c.dinner_add_price} / -¥${c.dinner_remove_price}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">交通費（バス）</div>
            <div class="card-body">
                <div class="row">
                    ${c.bus_fee_separate ?
                        `<div class="col-md-3"><strong>往路バス:</strong> ¥${Number(c.bus_fee_outbound || 0).toLocaleString()}</div>
                         <div class="col-md-3"><strong>復路バス:</strong> ¥${Number(c.bus_fee_return || 0).toLocaleString()}</div>` :
                        `<div class="col-md-6"><strong>バス代（往復）:</strong> ¥${Number(c.bus_fee_round_trip || 0).toLocaleString()}</div>`
                    }
                    <div class="col-md-3"><strong>往路高速:</strong> ¥${Number(c.highway_fee_outbound || 0).toLocaleString()}</div>
                    <div class="col-md-3"><strong>復路高速:</strong> ¥${Number(c.highway_fee_return || 0).toLocaleString()}</div>
                </div>
            </div>
        </div>

        ${c.use_rental_car ? `
        <div class="card">
            <div class="card-header">レンタカー</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>レンタカー代:</strong> ¥${Number(c.rental_car_fee || 0).toLocaleString()}</div>
                    <div class="col-md-4"><strong>高速代:</strong> ¥${Number(c.rental_car_highway_fee || 0).toLocaleString()}</div>
                    <div class="col-md-4"><strong>定員:</strong> ${c.rental_car_capacity || '-'}人</div>
                </div>
            </div>
        </div>
        ` : ''}
    `;
}

function renderSchedule() {
    const slots = campData.time_slots || [];
    const days = parseInt(campData.nights) + 1;
    const slotNames = { outbound: '往路', morning: '午前', afternoon: '午後', banquet: '宴会', return: '復路' };
    const courtFeePerUnit = campData.court_fee_per_unit || 0;
    const gymFeePerUnit = campData.gym_fee_per_unit || 0;
    const banquetFeePerPerson = campData.banquet_fee_per_person || 0;

    let html = `
        <div class="alert alert-info mb-3">
            <small>各スロットの施設種別を設定できます。コート料金は1面あたり¥${courtFeePerUnit.toLocaleString()}、体育館は1コマ¥${gymFeePerUnit.toLocaleString()}、宴会場は1人あたり¥${banquetFeePerPerson.toLocaleString()}で計算されます。変更後は「保存」ボタンをクリックしてください。</small>
        </div>
        <div class="card">
            <div class="card-body">
    `;

    for (let day = 1; day <= days; day++) {
        const daySlots = slots.filter(s => s.day_number == day);
        html += `<h6 class="mt-3 mb-2">${day}日目</h6>`;
        html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-3">';
        html += '<thead class="table-light"><tr><th style="width:100px;">時間帯</th><th>施設種別</th><th style="width:100px;">面数</th><th style="width:120px;">小計</th></tr></thead><tbody>';

        for (const slot of daySlots) {
            const isTransport = slot.slot_type === 'outbound' || slot.slot_type === 'return';

            if (isTransport) {
                // 往路・復路は固定表示
                html += `<tr>
                    <td><strong>${slotNames[slot.slot_type]}</strong></td>
                    <td>バス移動</td>
                    <td>-</td>
                    <td>-</td>
                </tr>`;
            } else {
                const isTennis = slot.activity_type === 'tennis';
                const isGym = slot.activity_type === 'gym';
                const isBanquet = slot.activity_type === 'banquet';
                const courtCount = slot.court_count || 1;
                // テニス: 面数×単価、体育館: 1コマ単価、宴会場: 1人あたり単価（自動適用）、その他: 直接入力値
                let calculatedFee = 0;
                let feeDisplayText = '';
                if (isTennis) {
                    calculatedFee = courtFeePerUnit * courtCount;
                    feeDisplayText = `¥${calculatedFee.toLocaleString()}`;
                } else if (isGym) {
                    calculatedFee = gymFeePerUnit;
                    feeDisplayText = `¥${calculatedFee.toLocaleString()}`;
                } else if (isBanquet) {
                    calculatedFee = banquetFeePerPerson;
                    feeDisplayText = `¥${calculatedFee.toLocaleString()}/人`;
                } else {
                    calculatedFee = slot.facility_fee || 0;
                    feeDisplayText = `¥${calculatedFee.toLocaleString()}`;
                }
                // 金額直接入力が必要なのはその他・なしの場合のみ
                const needsDirectFeeInput = !isTennis && !isGym && !isBanquet;

                // 午前・午後・宴会は編集可能
                html += `<tr>
                    <td><strong>${slotNames[slot.slot_type]}</strong></td>
                    <td>
                        <select class="form-select form-select-sm slot-activity" data-slot-id="${slot.id}" onchange="updateCourtCountVisibility(${slot.id})">
                            <option value="">なし</option>
                            <option value="tennis" ${slot.activity_type === 'tennis' ? 'selected' : ''}>テニスコート</option>
                            <option value="gym" ${slot.activity_type === 'gym' ? 'selected' : ''}>体育館</option>
                            <option value="banquet" ${slot.activity_type === 'banquet' ? 'selected' : ''}>宴会場</option>
                            <option value="other" ${slot.activity_type === 'other' ? 'selected' : ''}>その他</option>
                        </select>
                    </td>
                    <td>
                        <div class="slot-court-count-container" data-slot-id="${slot.id}" style="display: ${isTennis ? 'block' : 'none'};">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control slot-court-count" data-slot-id="${slot.id}" value="${courtCount}" min="1" onchange="updateCalculatedFee(${slot.id})">
                                <span class="input-group-text">面</span>
                            </div>
                        </div>
                        <div class="slot-fee-container" data-slot-id="${slot.id}" style="display: ${needsDirectFeeInput ? 'block' : 'none'};">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control slot-fee" data-slot-id="${slot.id}" value="${slot.facility_fee || 0}" min="0" onchange="updateCalculatedFee(${slot.id})">
                            </div>
                        </div>
                        <div class="slot-auto-fee-label" data-slot-id="${slot.id}" style="display: ${isGym ? 'block' : 'none'};">
                            <small class="text-muted">単価自動適用</small>
                        </div>
                        <div class="slot-banquet-fee-label" data-slot-id="${slot.id}" style="display: ${isBanquet ? 'block' : 'none'};">
                            <small class="text-muted">1人あたり単価適用</small>
                        </div>
                    </td>
                    <td class="slot-calculated-fee" data-slot-id="${slot.id}">
                        ${feeDisplayText}
                    </td>
                </tr>`;
            }
        }

        // 宴会スロットがない場合は追加オプションを表示
        const hasBanquetSlot = daySlots.some(s => s.slot_type === 'banquet');
        if (!hasBanquetSlot && day < days) {
            html += `<tr class="table-secondary">
                <td><strong>宴会</strong></td>
                <td colspan="3">
                    <button class="btn btn-outline-primary btn-sm" onclick="addBanquetSlot(${day})">
                        + 宴会スロットを追加
                    </button>
                </td>
            </tr>`;
        }

        html += '</tbody></table></div>';
    }

    html += `
            </div>
            <div class="card-footer">
                <button class="btn btn-primary" onclick="saveSchedule()">変更を保存</button>
            </div>
        </div>
    `;

    document.getElementById('scheduleInfo').innerHTML = html;
}

function updateCourtCountVisibility(slotId) {
    const activitySelect = document.querySelector(`.slot-activity[data-slot-id="${slotId}"]`);
    const courtCountContainer = document.querySelector(`.slot-court-count-container[data-slot-id="${slotId}"]`);
    const feeContainer = document.querySelector(`.slot-fee-container[data-slot-id="${slotId}"]`);
    const autoFeeLabel = document.querySelector(`.slot-auto-fee-label[data-slot-id="${slotId}"]`);
    const banquetFeeLabel = document.querySelector(`.slot-banquet-fee-label[data-slot-id="${slotId}"]`);

    if (activitySelect && courtCountContainer && feeContainer) {
        const activityType = activitySelect.value;
        const isTennis = activityType === 'tennis';
        const isGym = activityType === 'gym';
        const isBanquet = activityType === 'banquet';
        const needsDirectFeeInput = !isTennis && !isGym && !isBanquet;

        courtCountContainer.style.display = isTennis ? 'block' : 'none';
        feeContainer.style.display = needsDirectFeeInput ? 'block' : 'none';
        if (autoFeeLabel) {
            autoFeeLabel.style.display = isGym ? 'block' : 'none';
        }
        if (banquetFeeLabel) {
            banquetFeeLabel.style.display = isBanquet ? 'block' : 'none';
        }
        updateCalculatedFee(slotId);
    }
}

function updateCalculatedFee(slotId) {
    const activitySelect = document.querySelector(`.slot-activity[data-slot-id="${slotId}"]`);
    const courtCountInput = document.querySelector(`.slot-court-count[data-slot-id="${slotId}"]`);
    const feeInput = document.querySelector(`.slot-fee[data-slot-id="${slotId}"]`);
    const calculatedFeeCell = document.querySelector(`.slot-calculated-fee[data-slot-id="${slotId}"]`);

    if (!calculatedFeeCell) return;

    const courtFeePerUnit = campData.court_fee_per_unit || 0;
    const gymFeePerUnit = campData.gym_fee_per_unit || 0;
    const banquetFeePerPerson = campData.banquet_fee_per_person || 0;
    const activityType = activitySelect ? activitySelect.value : '';

    let fee = 0;
    let displayText = '';
    if (activityType === 'tennis') {
        const courtCount = courtCountInput ? parseInt(courtCountInput.value) || 1 : 1;
        fee = courtFeePerUnit * courtCount;
        displayText = `¥${fee.toLocaleString()}`;
    } else if (activityType === 'gym') {
        fee = gymFeePerUnit;
        displayText = `¥${fee.toLocaleString()}`;
    } else if (activityType === 'banquet') {
        fee = banquetFeePerPerson;
        displayText = `¥${fee.toLocaleString()}/人`;
    } else {
        fee = feeInput ? parseInt(feeInput.value) || 0 : 0;
        displayText = `¥${fee.toLocaleString()}`;
    }

    calculatedFeeCell.textContent = displayText;
}

async function addBanquetSlot(dayNumber) {
    // 現在のスロットデータを取得
    const slots = [...(campData.time_slots || [])];

    // 新しい宴会スロットを追加
    slots.push({
        day_number: dayNumber,
        slot_type: 'banquet',
        activity_type: 'banquet',
        facility_fee: 0,
        court_count: 1,
        description: '宴会場'
    });

    // APIで保存
    try {
        const slotsToSave = slots.map(s => ({
            day_number: s.day_number,
            slot_type: s.slot_type,
            activity_type: s.activity_type,
            facility_fee: s.facility_fee || 0,
            court_count: s.court_count || 1,
            description: s.description || ''
        }));

        const res = await fetch(`/index.php?route=api/camps/${campId}/time-slots`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slots: slotsToSave })
        });

        const result = await res.json();

        if (result.success) {
            loadCampData();
            showToast('宴会スロットを追加しました');
        } else {
            alert(result.error?.message || '追加に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function saveSchedule() {
    const slots = [];
    const existingSlots = campData.time_slots || [];
    const courtFeePerUnit = campData.court_fee_per_unit || 0;
    const gymFeePerUnit = campData.gym_fee_per_unit || 0;
    const banquetFeePerPerson = campData.banquet_fee_per_person || 0;

    // 既存スロットのデータを収集
    for (const slot of existingSlots) {
        const activitySelect = document.querySelector(`.slot-activity[data-slot-id="${slot.id}"]`);
        const feeInput = document.querySelector(`.slot-fee[data-slot-id="${slot.id}"]`);
        const courtCountInput = document.querySelector(`.slot-court-count[data-slot-id="${slot.id}"]`);

        const activityType = activitySelect ? activitySelect.value : slot.activity_type;
        const courtCount = courtCountInput ? parseInt(courtCountInput.value) || 1 : (slot.court_count || 1);

        // テニス: 面数×単価、体育館: 1コマ単価、宴会場: 1人あたり単価、それ以外: 直接入力
        let facilityFee;
        if (activityType === 'tennis') {
            facilityFee = courtFeePerUnit * courtCount;
        } else if (activityType === 'gym') {
            facilityFee = gymFeePerUnit;
        } else if (activityType === 'banquet') {
            facilityFee = banquetFeePerPerson;
        } else {
            facilityFee = feeInput ? parseInt(feeInput.value) || 0 : (slot.facility_fee || 0);
        }

        slots.push({
            day_number: slot.day_number,
            slot_type: slot.slot_type,
            activity_type: activityType,
            facility_fee: facilityFee,
            court_count: courtCount,
            description: slot.description || ''
        });
    }

    try {
        const res = await fetch(`/index.php?route=api/camps/${campId}/time-slots`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slots })
        });

        const result = await res.json();

        if (result.success) {
            loadCampData();
            showToast('日程設定を保存しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function getGradeGenderLabel(grade, gender) {
    if (grade === null && gender === null) return '';
    if (grade === 0) {
        // OB/OGを性別で区別
        if (gender === 'male') return 'OB';
        if (gender === 'female') return 'OG';
        return 'OB/OG'; // 性別未設定の場合
    }
    const gradeStr = grade ? grade : '';
    const genderStr = gender === 'male' ? '男' : (gender === 'female' ? '女' : '');
    return gradeStr + genderStr;
}

function getSortedFilteredParticipants() {
    let participants = [...(campData.participants || [])];

    // 検索フィルタ
    const searchText = document.getElementById('participantSearch').value.toLowerCase();
    if (searchText) {
        participants = participants.filter(p => p.name.toLowerCase().includes(searchText));
    }

    // ソート（複合ソート対応）
    participants.sort((a, b) => {
        // 第1ソート
        let cmp = compareByKey(a, b, sortConfig.primary.key);
        if (cmp !== 0) return cmp * sortConfig.primary.direction;

        // 第2ソート
        if (sortConfig.secondary.key) {
            cmp = compareByKey(a, b, sortConfig.secondary.key);
            if (cmp !== 0) return cmp * sortConfig.secondary.direction;
        }

        // 最終的に名前でソート
        return (a.name || '').localeCompare(b.name || '', 'ja');
    });

    return participants;
}

// ソートキーによる比較関数
function compareByKey(a, b, key) {
    if (key === 'name') {
        return (a.name || '').localeCompare(b.name || '', 'ja');
    } else if (key === 'grade') {
        const gradeA = a.grade === null ? 999 : (a.grade === 0 ? 99 : a.grade);
        const gradeB = b.grade === null ? 999 : (b.grade === 0 ? 99 : b.grade);
        return gradeA - gradeB;
    } else if (key === 'gender') {
        const genderOrder = { 'male': 1, 'female': 2, null: 3 };
        return (genderOrder[a.gender] || 3) - (genderOrder[b.gender] || 3);
    }
    return 0;
}

// ソート方向切り替え
function toggleSortDirection(level) {
    if (level === 'primary') {
        sortConfig.primary.direction *= -1;
        document.getElementById('sortPrimaryDir').textContent = sortConfig.primary.direction === 1 ? '↑' : '↓';
    } else {
        sortConfig.secondary.direction *= -1;
        document.getElementById('sortSecondaryDir').textContent = sortConfig.secondary.direction === 1 ? '↑' : '↓';
    }
    renderParticipants();
}

// ソート適用
function applySorting() {
    sortConfig.primary.key = document.getElementById('sortPrimary').value;
    sortConfig.secondary.key = document.getElementById('sortSecondary').value || '';
    renderParticipants();
}

function renderParticipants() {
    const allParticipants = campData.participants || [];
    document.getElementById('participantCount').textContent = allParticipants.length;

    const participants = getSortedFilteredParticipants();

    if (allParticipants.length === 0) {
        document.getElementById('participantList').innerHTML = '<p class="text-muted">参加者がいません</p>';
        return;
    }

    if (participants.length === 0) {
        document.getElementById('participantList').innerHTML = '<p class="text-muted">該当する参加者がいません</p>';
        return;
    }

    const days = parseInt(campData.nights) + 1;
    const joinTimingLabels = {
        outbound_bus: '往路バス', breakfast: '朝食', morning: '午前イベント', lunch: '昼食',
        afternoon: '午後イベント', dinner: '夕食', night: '夜', lodging: '宿泊'
    };
    const leaveTimingLabels = {
        return_bus: '復路バス', before_breakfast: '朝食前', breakfast: '朝食', morning: '午前イベント',
        lunch: '昼食', afternoon: '午後イベント', dinner: '夕食', night: '夜'
    };

    let html = '<table class="table table-hover"><thead><tr><th>名前</th><th>学年</th><th>参加期間</th><th>交通</th><th></th></tr></thead><tbody>';

    for (const p of participants) {
        const joinLabel = `${p.join_day}日目${joinTimingLabels[p.join_timing] || ''}`;
        const leaveLabel = `${p.leave_day}日目${leaveTimingLabels[p.leave_timing] || ''}`;

        let transportLabel = '';
        if (p.use_rental_car) {
            transportLabel = 'レンタカー';
        } else if (p.use_outbound_bus && p.use_return_bus) {
            transportLabel = 'バス往復';
        } else if (p.use_outbound_bus) {
            transportLabel = 'バス往路のみ';
        } else if (p.use_return_bus) {
            transportLabel = 'バス復路のみ';
        } else {
            transportLabel = 'なし';
        }

        const isFullParticipation = (p.join_day == 1 && p.join_timing == 'outbound_bus' && p.leave_day == days && p.leave_timing == 'return_bus');
        const gradeGenderLabel = getGradeGenderLabel(p.grade, p.gender);

        html += `<tr>
            <td>${escapeHtml(p.name)}</td>
            <td>${gradeGenderLabel}</td>
            <td>${isFullParticipation ? 'フル参加' : `${joinLabel}～${leaveLabel}`}</td>
            <td>${transportLabel}</td>
            <td><button class="btn btn-outline-primary btn-sm" onclick="editParticipant(${p.id})">編集</button></td>
        </tr>`;
    }

    html += '</tbody></table>';
    document.getElementById('participantList').innerHTML = html;
}

function filterParticipants() {
    renderParticipants();
}

function showCsvImportModal() {
    document.getElementById('csvData').value = '';
    document.getElementById('csvFile').value = '';
    document.getElementById('csvImportResult').classList.add('d-none');
    csvImportModal.show();
}

function loadCsvFile(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('csvData').value = e.target.result;
    };
    reader.onerror = function() {
        alert('ファイルの読み込みに失敗しました');
    };
    // Shift-JISも考慮してテキストとして読み込む
    reader.readAsText(file, 'UTF-8');
}

async function deleteAllParticipants() {
    const participants = campData.participants || [];
    if (participants.length === 0) {
        alert('削除する参加者がいません');
        return;
    }

    if (!confirm(`参加者${participants.length}名を全員削除しますか？\n\n※この操作は取り消せません。`)) return;

    try {
        const res = await fetch(`/index.php?route=api/camps/${campId}/participants/deleteAll`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();

        if (data.success) {
            showToast('参加者を全員削除しました');
            loadCampData();
        } else {
            alert(data.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function importCsv() {
    const csvData = document.getElementById('csvData').value.trim();

    if (!csvData) {
        alert('CSVデータを入力してください');
        return;
    }

    try {
        const res = await fetch(`/index.php?route=api/camps/${campId}/participants/import`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csv_data: csvData })
        });

        const result = await res.json();

        const resultDiv = document.getElementById('csvImportResult');
        const successDiv = document.getElementById('csvSuccessMsg');
        const errorDiv = document.getElementById('csvErrorMsg');

        resultDiv.classList.remove('d-none');

        if (result.success) {
            successDiv.textContent = `${result.data.success_count}名の参加者を登録しました`;
            successDiv.classList.remove('d-none');

            if (result.data.errors && result.data.errors.length > 0) {
                errorDiv.innerHTML = result.data.errors.join('<br>');
                errorDiv.classList.remove('d-none');
            } else {
                errorDiv.classList.add('d-none');
            }

            loadCampData();
        } else {
            successDiv.classList.add('d-none');
            errorDiv.textContent = result.error?.message || 'インポートに失敗しました';
            errorDiv.classList.remove('d-none');
        }

    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function renderExpenses() {
    const expenses = campData.expenses || [];
    const days = parseInt(campData.nights) + 1;
    const slotTypes = ['morning', 'afternoon', 'banquet'];
    const slotNames = { morning: '午前', afternoon: '午後', banquet: '宴会' };

    // 時間割テーブルのヘッダーを生成
    let headerHtml = '<tr><th style="width:80px;"></th>';
    for (let day = 1; day <= days; day++) {
        headerHtml += `<th class="text-center">${day}日目</th>`;
    }
    headerHtml += '</tr>';
    document.querySelector('#expenseScheduleTable thead').innerHTML = headerHtml;

    // 時間割テーブルの本体を生成
    let bodyHtml = '';
    for (const slotType of slotTypes) {
        bodyHtml += `<tr><th class="table-light">${slotNames[slotType]}</th>`;
        for (let day = 1; day <= days; day++) {
            // このスロットの雑費を取得
            const slotExpenses = expenses.filter(e =>
                e.target_type === 'slot' && e.target_day == day && e.target_slot === slotType
            );

            let cellContent = '';
            if (slotExpenses.length > 0) {
                cellContent = slotExpenses.map(e =>
                    `<div class="expense-item mb-1" onclick="editExpense(${e.id}); event.stopPropagation();">
                        <small class="text-primary" style="cursor:pointer;">
                            ${escapeHtml(e.name)}<br>
                            <span class="text-muted">¥${Number(e.amount).toLocaleString()}</span>
                            ${e.payer_name ? `<br><span class="text-info">[${escapeHtml(e.payer_name)}]</span>` : ''}
                        </small>
                    </div>`
                ).join('');
            }

            bodyHtml += `<td class="expense-cell" style="cursor:pointer; min-height:60px; vertical-align:top; padding:8px;"
                            onclick="showExpenseModalForSlot(${day}, '${slotType}')"
                            data-day="${day}" data-slot="${slotType}">
                ${cellContent}
                <div class="add-expense-hint text-muted" style="font-size:0.75rem;">
                    ${slotExpenses.length === 0 ? '<i>+ クリックで追加</i>' : '<i>+ 追加</i>'}
                </div>
            </td>`;
        }
        bodyHtml += '</tr>';
    }
    document.querySelector('#expenseScheduleTable tbody').innerHTML = bodyHtml;

    // 全員対象の雑費一覧
    const allExpenses = expenses.filter(e => e.target_type === 'all' || !e.target_type);

    if (allExpenses.length === 0) {
        document.getElementById('expenseListAll').innerHTML = '<p class="text-muted">全員対象の雑費はありません</p>';
    } else {
        let html = '<table class="table table-hover"><thead><tr><th>項目名</th><th>金額</th><th>建て替え</th><th></th></tr></thead><tbody>';
        for (const e of allExpenses) {
            html += `<tr>
                <td>${escapeHtml(e.name)}</td>
                <td>¥${Number(e.amount).toLocaleString()}</td>
                <td>${e.payer_name ? escapeHtml(e.payer_name) : '<span class="text-muted">-</span>'}</td>
                <td><button class="btn btn-outline-primary btn-sm" onclick="editExpense(${e.id})">編集</button></td>
            </tr>`;
        }
        html += '</tbody></table>';
        document.getElementById('expenseListAll').innerHTML = html;
    }

    // 建て替え総額集計を表示
    renderPayerSummary();
}

function renderPayerSummary() {
    const expenses = campData.expenses || [];

    // 建て替え者ごとに集計
    const payerTotals = {};
    for (const e of expenses) {
        if (e.payer_id && e.payer_name) {
            if (!payerTotals[e.payer_id]) {
                payerTotals[e.payer_id] = {
                    name: e.payer_name,
                    total: 0,
                    items: []
                };
            }
            payerTotals[e.payer_id].total += Number(e.amount);
            payerTotals[e.payer_id].items.push(e);
        }
    }

    const payerIds = Object.keys(payerTotals);

    if (payerIds.length === 0) {
        document.getElementById('payerSummary').innerHTML = '<p class="text-muted">建て替えが登録されている雑費はありません</p>';
        return;
    }

    // 合計金額でソート（降順）
    payerIds.sort((a, b) => payerTotals[b].total - payerTotals[a].total);

    let html = '<div class="card"><div class="card-body p-0"><table class="table table-hover mb-0">';
    html += '<thead class="table-light"><tr><th>建て替え者</th><th class="text-end">建て替え総額</th><th>内訳</th></tr></thead><tbody>';

    for (const id of payerIds) {
        const payer = payerTotals[id];
        const itemsDetail = payer.items.map(e => `${escapeHtml(e.name)}(¥${Number(e.amount).toLocaleString()})`).join('、');

        html += `<tr>
            <td><strong>${escapeHtml(payer.name)}</strong></td>
            <td class="text-end"><strong>¥${payer.total.toLocaleString()}</strong></td>
            <td><small class="text-muted">${itemsDetail}</small></td>
        </tr>`;
    }

    // 合計行
    const grandTotal = payerIds.reduce((sum, id) => sum + payerTotals[id].total, 0);
    html += `<tr class="table-secondary">
        <td><strong>合計</strong></td>
        <td class="text-end"><strong>¥${grandTotal.toLocaleString()}</strong></td>
        <td></td>
    </tr>`;

    html += '</tbody></table></div></div>';
    document.getElementById('payerSummary').innerHTML = html;
}

function showExpenseModalForSlot(day, slotType) {
    const slotNames = { morning: '午前', afternoon: '午後', banquet: '宴会' };
    document.getElementById('expenseModalTitle').textContent = `雑費追加（${day}日目${slotNames[slotType]}）`;
    document.getElementById('expenseId').value = '';
    document.getElementById('expenseForm').reset();
    document.getElementById('deleteExpenseBtn').style.display = 'none';

    // 特定タイミングを選択
    document.getElementById('targetSlot').checked = true;
    toggleTargetSlot();
    setupDaySelectors();

    // 日程とスロットを事前設定
    document.getElementById('expenseTargetDay').value = day;
    document.getElementById('expenseTargetSlot').value = slotType;

    expenseModal.show();
}

function setupDaySelectors() {
    const days = parseInt(campData.nights) + 1;
    const selectors = ['joinDay', 'leaveDay', 'expenseTargetDay'];

    for (const id of selectors) {
        const el = document.getElementById(id);
        if (el) {
            el.innerHTML = '';
            for (let i = 1; i <= days; i++) {
                el.innerHTML += `<option value="${i}">${i}</option>`;
            }
            if (id === 'leaveDay') el.value = days;
        }
    }

    // 日程変更時にタイミング選択肢を更新
    document.getElementById('joinDay').addEventListener('change', updateJoinTimingOptions);
    document.getElementById('leaveDay').addEventListener('change', updateLeaveTimingOptions);

    // 初期設定
    updateJoinTimingOptions();
    updateLeaveTimingOptions();

    // 建て替え者セレクタを更新
    setupPayerSelector();
}

// 参加開始タイミングの選択肢を更新
function updateJoinTimingOptions() {
    const joinDay = parseInt(document.getElementById('joinDay').value);
    const joinTimingSelect = document.getElementById('joinTiming');
    const currentValue = joinTimingSelect.value;
    const days = parseInt(campData.nights) + 1;

    // 選択肢を生成
    // 食事と活動それぞれの境界で区別
    let options = [];

    // 1日目のみ「往路バスから」を表示
    if (joinDay === 1) {
        options.push({ value: 'outbound_bus', label: '往路バスから' });
    }

    // 共通の選択肢
    options.push({ value: 'breakfast', label: '朝食から' });           // 朝食から参加
    options.push({ value: 'morning', label: '午前イベントから' });     // 朝食を食べずに午前イベントから参加
    options.push({ value: 'lunch', label: '昼食から' });               // 昼食から参加
    options.push({ value: 'afternoon', label: '午後イベントから' });   // 昼食を食べずに午後イベントから参加
    options.push({ value: 'dinner', label: '夕食から' });              // 夕食から参加
    options.push({ value: 'night', label: '夜から' });                 // 夕食を食べずに夜イベントから参加
    options.push({ value: 'lodging', label: '宿泊から' });             // 宿泊のみ参加

    // セレクトボックスを更新
    joinTimingSelect.innerHTML = options.map(opt =>
        `<option value="${opt.value}">${opt.label}</option>`
    ).join('');

    // 可能であれば以前の値を復元
    const validValues = options.map(opt => opt.value);
    if (validValues.includes(currentValue)) {
        joinTimingSelect.value = currentValue;
    } else if (joinDay === 1) {
        joinTimingSelect.value = 'outbound_bus'; // 1日目のデフォルトは往路バス
    } else {
        joinTimingSelect.value = 'breakfast'; // 途中参加のデフォルトは朝食
    }

    // バス設定を自動更新
    updateBusFromTiming();
}

// 参加終了タイミングの選択肢を更新
function updateLeaveTimingOptions() {
    const leaveDay = parseInt(document.getElementById('leaveDay').value);
    const leaveTimingSelect = document.getElementById('leaveTiming');
    const currentValue = leaveTimingSelect.value;
    const days = parseInt(campData.nights) + 1;

    // 選択肢を生成
    // 食事と活動それぞれの境界で区別
    let options = [];

    // 共通の選択肢（最終日以外）
    options.push({ value: 'before_breakfast', label: '朝食前まで' });  // 朝食を食べずに帰る
    options.push({ value: 'breakfast', label: '朝食まで' });           // 朝食を食べて午前イベントに参加せず帰る
    options.push({ value: 'morning', label: '午前イベントまで' });     // 午前イベントに参加して昼食を食べずに帰る
    options.push({ value: 'lunch', label: '昼食まで' });               // 昼食を食べて午後イベントに参加せず帰る

    // 最終日以外のみ午後イベント・夕食・夜を表示（最終日は昼食後バスで帰るため）
    // 「宿泊まで」は翌日の「朝食前まで」と同義なので選択肢から削除
    if (leaveDay < days) {
        options.push({ value: 'afternoon', label: '午後イベントまで' });   // 午後イベントに参加して夕食を食べずに帰る
        options.push({ value: 'dinner', label: '夕食まで' });          // 夕食を食べて夜イベントに参加せず帰る
        options.push({ value: 'night', label: '夜まで' });             // 夜イベントに参加して宿泊せずに帰る
    }

    // 最終日のみ「復路バスまで」を表示
    if (leaveDay === days) {
        options.push({ value: 'return_bus', label: '復路バスまで' });
    }

    // セレクトボックスを更新
    leaveTimingSelect.innerHTML = options.map(opt =>
        `<option value="${opt.value}">${opt.label}</option>`
    ).join('');

    // 可能であれば以前の値を復元
    const validValues = options.map(opt => opt.value);
    if (validValues.includes(currentValue)) {
        leaveTimingSelect.value = currentValue;
    } else if (leaveDay === days) {
        leaveTimingSelect.value = 'return_bus'; // 最終日のデフォルトは復路バス
    } else {
        leaveTimingSelect.value = 'night'; // 途中抜けのデフォルトは夜まで
    }

    // バス設定を自動更新
    updateBusFromTiming();
}

// タイミング変更時にバス使用を自動設定
function updateBusFromTiming() {
    const joinDay = parseInt(document.getElementById('joinDay').value);
    const joinTiming = document.getElementById('joinTiming').value;
    const leaveDay = parseInt(document.getElementById('leaveDay').value);
    const leaveTiming = document.getElementById('leaveTiming').value;
    const days = parseInt(campData.nights) + 1;

    // 往路バス: 1日目で「往路バスから」を選択した場合はON、それ以外はOFF
    const useOutbound = (joinDay === 1 && joinTiming === 'outbound_bus');
    document.getElementById('useOutboundBus').checked = useOutbound;

    // 復路バス: 最終日で「復路バスまで」を選択した場合はON、それ以外はOFF
    const useReturn = (leaveDay === days && leaveTiming === 'return_bus');
    document.getElementById('useReturnBus').checked = useReturn;
}

function setupPayerSelector() {
    const payerSelect = document.getElementById('expensePayerId');
    if (!payerSelect) return;

    const participants = campData.participants || [];

    payerSelect.innerHTML = '<option value="">なし（未指定）</option>';
    for (const p of participants) {
        payerSelect.innerHTML += `<option value="${p.id}">${escapeHtml(p.name)}</option>`;
    }
}

function showParticipantModal() {
    document.getElementById('participantModalTitle').textContent = '参加者追加';
    document.getElementById('participantId').value = '';
    document.getElementById('participantForm').reset();
    document.getElementById('deleteParticipantBtn').style.display = 'none';
    setupDaySelectors();
    document.getElementById('leaveDay').value = parseInt(campData.nights) + 1;

    // レンタカーオプションの表示/非表示
    const rentalCarOption = document.getElementById('rentalCarOption');
    if (campData.use_rental_car) {
        rentalCarOption.style.display = 'block';
    } else {
        rentalCarOption.style.display = 'none';
    }

    participantModal.show();
}

function editParticipant(id) {
    const p = campData.participants.find(x => x.id == id);
    if (!p) return;

    document.getElementById('participantModalTitle').textContent = '参加者編集';
    document.getElementById('participantId').value = p.id;
    document.getElementById('participantName').value = p.name;
    document.getElementById('participantGrade').value = p.grade !== null ? p.grade : '';
    document.getElementById('participantGender').value = p.gender || '';

    // 日程セレクタをセットアップ（イベントリスナーなしで）
    const days = parseInt(campData.nights) + 1;
    const joinDaySelect = document.getElementById('joinDay');
    const leaveDaySelect = document.getElementById('leaveDay');
    const expenseTargetDaySelect = document.getElementById('expenseTargetDay');

    for (const el of [joinDaySelect, leaveDaySelect, expenseTargetDaySelect]) {
        if (el) {
            el.innerHTML = '';
            for (let i = 1; i <= days; i++) {
                el.innerHTML += `<option value="${i}">${i}</option>`;
            }
        }
    }

    // 日程を設定
    joinDaySelect.value = p.join_day;
    leaveDaySelect.value = p.leave_day;

    // タイミング選択肢を更新（バス自動設定なしで）
    updateJoinTimingOptionsWithoutBusUpdate();
    updateLeaveTimingOptionsWithoutBusUpdate();

    // タイミングを設定
    document.getElementById('joinTiming').value = p.join_timing;
    document.getElementById('leaveTiming').value = p.leave_timing;

    // 既存のバス設定を復元（自動設定を上書き）
    document.getElementById('useOutboundBus').checked = p.use_outbound_bus == 1;
    document.getElementById('useReturnBus').checked = p.use_return_bus == 1;
    document.getElementById('useRentalCar').checked = p.use_rental_car == 1;
    document.getElementById('deleteParticipantBtn').style.display = 'block';

    // イベントリスナーを再設定
    joinDaySelect.onchange = function() {
        updateJoinTimingOptions();
    };
    leaveDaySelect.onchange = function() {
        updateLeaveTimingOptions();
    };

    // レンタカーオプションの表示/非表示
    const rentalCarOption = document.getElementById('rentalCarOption');
    if (campData.use_rental_car) {
        rentalCarOption.style.display = 'block';
    } else {
        rentalCarOption.style.display = 'none';
    }

    // 建て替え者セレクタを更新
    setupPayerSelector();

    participantModal.show();
}

// タイミング選択肢を更新（バス自動設定なし - 編集時用）
function updateJoinTimingOptionsWithoutBusUpdate() {
    const joinDay = parseInt(document.getElementById('joinDay').value);
    const joinTimingSelect = document.getElementById('joinTiming');

    // 食事と活動それぞれの境界で区別
    let options = [];
    if (joinDay === 1) {
        options.push({ value: 'outbound_bus', label: '往路バスから' });
    }
    options.push({ value: 'breakfast', label: '朝食から' });
    options.push({ value: 'morning', label: '午前イベントから' });
    options.push({ value: 'lunch', label: '昼食から' });
    options.push({ value: 'afternoon', label: '午後イベントから' });
    options.push({ value: 'dinner', label: '夕食から' });
    options.push({ value: 'night', label: '夜から' });
    options.push({ value: 'lodging', label: '宿泊から' });

    joinTimingSelect.innerHTML = options.map(opt =>
        `<option value="${opt.value}">${opt.label}</option>`
    ).join('');
}

function updateLeaveTimingOptionsWithoutBusUpdate() {
    const leaveDay = parseInt(document.getElementById('leaveDay').value);
    const leaveTimingSelect = document.getElementById('leaveTiming');
    const days = parseInt(campData.nights) + 1;

    // 食事と活動それぞれの境界で区別
    // 「宿泊まで」は翌日の「朝食前まで」と同義なので選択肢から削除
    // 最終日は昼食後にバスで帰るため、午後イベント以降の選択肢は不要
    let options = [];
    options.push({ value: 'before_breakfast', label: '朝食前まで' });
    options.push({ value: 'breakfast', label: '朝食まで' });
    options.push({ value: 'morning', label: '午前イベントまで' });
    options.push({ value: 'lunch', label: '昼食まで' });
    if (leaveDay < days) {
        options.push({ value: 'afternoon', label: '午後イベントまで' });
        options.push({ value: 'dinner', label: '夕食まで' });
        options.push({ value: 'night', label: '夜まで' });
    }
    if (leaveDay === days) {
        options.push({ value: 'return_bus', label: '復路バスまで' });
    }

    leaveTimingSelect.innerHTML = options.map(opt =>
        `<option value="${opt.value}">${opt.label}</option>`
    ).join('');
}

async function saveParticipant() {
    const id = document.getElementById('participantId').value;
    const gradeVal = document.getElementById('participantGrade').value;
    const genderVal = document.getElementById('participantGender').value;

    const data = {
        name: document.getElementById('participantName').value,
        grade: gradeVal !== '' ? parseInt(gradeVal) : null,
        gender: genderVal || null,
        join_day: parseInt(document.getElementById('joinDay').value),
        join_timing: document.getElementById('joinTiming').value,
        leave_day: parseInt(document.getElementById('leaveDay').value),
        leave_timing: document.getElementById('leaveTiming').value,
        use_outbound_bus: document.getElementById('useOutboundBus').checked ? 1 : 0,
        use_return_bus: document.getElementById('useReturnBus').checked ? 1 : 0,
        use_rental_car: document.getElementById('useRentalCar').checked ? 1 : 0,
    };

    const url = id ? `/index.php?route=api/participants/${id}` : `/index.php?route=api/camps/${campId}/participants`;
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            participantModal.hide();
            loadCampData();
            showToast('保存しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteParticipant() {
    const id = document.getElementById('participantId').value;
    if (!id || !confirm('この参加者を削除しますか？')) return;

    try {
        const res = await fetch(`/index.php?route=api/participants/${id}`, { method: 'DELETE' });
        const result = await res.json();

        if (result.success) {
            participantModal.hide();
            loadCampData();
            showToast('削除しました');
        } else {
            alert(result.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function showExpenseModal() {
    document.getElementById('expenseModalTitle').textContent = '雑費追加';
    document.getElementById('expenseId').value = '';
    document.getElementById('expenseForm').reset();
    document.getElementById('deleteExpenseBtn').style.display = 'none';
    document.getElementById('targetAll').checked = true;
    toggleTargetSlot();
    setupDaySelectors();
    expenseModal.show();
}

function editExpense(id) {
    const e = campData.expenses.find(x => x.id == id);
    if (!e) return;

    document.getElementById('expenseModalTitle').textContent = '雑費編集';
    document.getElementById('expenseId').value = e.id;
    document.getElementById('expenseName').value = e.name;
    document.getElementById('expenseAmount').value = e.amount;
    setupDaySelectors();

    if (e.target_type === 'slot') {
        document.getElementById('targetSlot').checked = true;
        document.getElementById('expenseTargetDay').value = e.target_day;
        document.getElementById('expenseTargetSlot').value = e.target_slot;
    } else {
        document.getElementById('targetAll').checked = true;
    }
    toggleTargetSlot();

    // 建て替え者を設定
    document.getElementById('expensePayerId').value = e.payer_id || '';

    document.getElementById('deleteExpenseBtn').style.display = 'block';
    expenseModal.show();
}

function toggleTargetSlot() {
    const isSlot = document.getElementById('targetSlot').checked;
    document.getElementById('targetSlotInputs').style.display = isSlot ? 'flex' : 'none';
}

async function saveExpense() {
    const id = document.getElementById('expenseId').value;
    const targetType = document.querySelector('input[name="targetType"]:checked').value;
    const payerIdValue = document.getElementById('expensePayerId').value;

    const data = {
        name: document.getElementById('expenseName').value,
        amount: parseInt(document.getElementById('expenseAmount').value),
        target_type: targetType,
        target_day: targetType === 'slot' ? parseInt(document.getElementById('expenseTargetDay').value) : null,
        target_slot: targetType === 'slot' ? document.getElementById('expenseTargetSlot').value : null,
        payer_id: payerIdValue ? parseInt(payerIdValue) : null,
    };

    const url = id ? `/index.php?route=api/expenses/${id}` : `/index.php?route=api/camps/${campId}/expenses`;
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            expenseModal.hide();
            loadCampData();
            showToast('保存しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteExpense() {
    const id = document.getElementById('expenseId').value;
    if (!id || !confirm('この雑費を削除しますか？')) return;

    try {
        const res = await fetch(`/index.php?route=api/expenses/${id}`, { method: 'DELETE' });
        const result = await res.json();

        if (result.success) {
            expenseModal.hide();
            loadCampData();
            showToast('削除しました');
        } else {
            alert(result.error?.message || '削除に失敗しました');
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

function showEditBasicInfoModal() {
    const c = campData;

    // フォームに現在の値を設定
    document.getElementById('editCampName').value = c.name || '';
    document.getElementById('editCourtFeePerUnit').value = c.court_fee_per_unit || '';
    document.getElementById('editGymFeePerUnit').value = c.gym_fee_per_unit || '';
    document.getElementById('editBanquetFeePerPerson').value = c.banquet_fee_per_person || '';
    document.getElementById('editLodgingFee').value = c.lodging_fee_per_night || 0;
    document.getElementById('editInsuranceFee').value = c.insurance_fee || 0;
    document.getElementById('editFirstDayLunch').value = c.first_day_lunch_included ? '1' : '0';

    // 食事単価
    document.getElementById('editBreakfastAdd').value = c.breakfast_add_price || 0;
    document.getElementById('editBreakfastRemove').value = c.breakfast_remove_price || 0;
    document.getElementById('editLunchAdd').value = c.lunch_add_price || 0;
    document.getElementById('editLunchRemove').value = c.lunch_remove_price || 0;
    document.getElementById('editDinnerAdd').value = c.dinner_add_price || 0;
    document.getElementById('editDinnerRemove').value = c.dinner_remove_price || 0;

    // バス代
    document.getElementById('editBusFeeSeparate').checked = c.bus_fee_separate == 1;
    document.getElementById('editBusFeeRoundTrip').value = c.bus_fee_round_trip || 0;
    document.getElementById('editBusFeeOutbound').value = c.bus_fee_outbound || 0;
    document.getElementById('editBusFeeReturn').value = c.bus_fee_return || 0;
    document.getElementById('editHighwayFeeOutbound').value = c.highway_fee_outbound || 0;
    document.getElementById('editHighwayFeeReturn').value = c.highway_fee_return || 0;
    toggleEditBusFeeFields();

    // レンタカー
    document.getElementById('editUseRentalCar').checked = c.use_rental_car == 1;
    document.getElementById('editRentalCarFee').value = c.rental_car_fee || 0;
    document.getElementById('editRentalCarHighwayFee').value = c.rental_car_highway_fee || 0;
    document.getElementById('editRentalCarCapacity').value = c.rental_car_capacity || '';
    toggleEditRentalCarFields();

    basicInfoModal.show();
}

function toggleEditBusFeeFields() {
    const isSeparate = document.getElementById('editBusFeeSeparate').checked;
    document.getElementById('editBusFeeRoundTripField').style.display = isSeparate ? 'none' : 'flex';
    document.getElementById('editBusFeeSeparateFields').style.display = isSeparate ? 'flex' : 'none';
}

function toggleEditRentalCarFields() {
    const useRentalCar = document.getElementById('editUseRentalCar').checked;
    document.getElementById('editRentalCarFields').style.display = useRentalCar ? 'block' : 'none';
}

async function saveBasicInfo() {
    const name = document.getElementById('editCampName').value.trim();
    if (!name) {
        alert('合宿名を入力してください');
        return;
    }

    const data = {
        name: name,
        court_fee_per_unit: parseInt(document.getElementById('editCourtFeePerUnit').value) || null,
        gym_fee_per_unit: parseInt(document.getElementById('editGymFeePerUnit').value) || null,
        banquet_fee_per_person: parseInt(document.getElementById('editBanquetFeePerPerson').value) || null,
        lodging_fee_per_night: parseInt(document.getElementById('editLodgingFee').value) || 0,
        insurance_fee: parseInt(document.getElementById('editInsuranceFee').value) || 0,
        first_day_lunch_included: parseInt(document.getElementById('editFirstDayLunch').value),
        breakfast_add_price: parseInt(document.getElementById('editBreakfastAdd').value) || 0,
        breakfast_remove_price: parseInt(document.getElementById('editBreakfastRemove').value) || 0,
        lunch_add_price: parseInt(document.getElementById('editLunchAdd').value) || 0,
        lunch_remove_price: parseInt(document.getElementById('editLunchRemove').value) || 0,
        dinner_add_price: parseInt(document.getElementById('editDinnerAdd').value) || 0,
        dinner_remove_price: parseInt(document.getElementById('editDinnerRemove').value) || 0,
        bus_fee_separate: document.getElementById('editBusFeeSeparate').checked ? 1 : 0,
        bus_fee_round_trip: parseInt(document.getElementById('editBusFeeRoundTrip').value) || null,
        bus_fee_outbound: parseInt(document.getElementById('editBusFeeOutbound').value) || null,
        bus_fee_return: parseInt(document.getElementById('editBusFeeReturn').value) || null,
        highway_fee_outbound: parseInt(document.getElementById('editHighwayFeeOutbound').value) || null,
        highway_fee_return: parseInt(document.getElementById('editHighwayFeeReturn').value) || null,
        use_rental_car: document.getElementById('editUseRentalCar').checked ? 1 : 0,
        rental_car_fee: parseInt(document.getElementById('editRentalCarFee').value) || null,
        rental_car_highway_fee: parseInt(document.getElementById('editRentalCarHighwayFee').value) || null,
        rental_car_capacity: parseInt(document.getElementById('editRentalCarCapacity').value) || null,
    };

    try {
        const res = await fetch(`/index.php?route=api/camps/${campId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            basicInfoModal.hide();
            // ページタイトルも更新
            document.getElementById('campTitle').textContent = name;
            loadCampData();
            showToast('基本情報を保存しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function showToast(message) {
    // 簡易的なトースト表示（既存のものがあればそれを使う）
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '11';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-body">${escapeHtml(message)}</div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<style>
.expense-cell:hover {
    background-color: #f8f9fa;
}
.expense-cell .add-expense-hint {
    opacity: 0.5;
    transition: opacity 0.2s;
}
.expense-cell:hover .add-expense-hint {
    opacity: 1;
}
.expense-item:hover {
    background-color: #e9ecef;
    border-radius: 4px;
}
#expenseScheduleTable td {
    min-width: 120px;
}
</style>
