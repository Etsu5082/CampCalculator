<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-2">会員名簿管理</h1>
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0">年度:</label>
            <select class="form-select form-select-sm" id="academicYearSelect" style="width: 150px;" onchange="loadMembers()">
                <option value="">読み込み中...</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="showNewYearModal()">
                <i class="bi bi-plus-circle"></i> 新年度作成
            </button>
        </div>
    </div>
    <div>
        <button class="btn btn-outline-primary me-2" onclick="showImportModal()">
            <i class="bi bi-upload"></i> インポート
        </button>
        <button class="btn btn-primary" onclick="showCreateModal()">
            + 会員を追加
        </button>
    </div>
</div>

<!-- 検索・フィルタ -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">検索</label>
                <input type="text" class="form-control" id="searchQuery" placeholder="名前またはカナで検索..." oninput="debounceSearch()">
            </div>
            <div class="col-md-2">
                <label class="form-label">学年</label>
                <select class="form-select" id="filterGrade" onchange="loadMembers()">
                    <option value="">すべて</option>
                    <option value="1">1年</option>
                    <option value="2">2年</option>
                    <option value="3">3年</option>
                    <option value="4">4年</option>
                    <option value="5">5年</option>
                    <option value="6">6年</option>
                    <option value="M1">M1</option>
                    <option value="M2">M2</option>
                    <option value="OB">OB</option>
                    <option value="OG">OG</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">学部</label>
                <select class="form-select" id="filterFaculty" onchange="loadMembers()">
                    <option value="">すべて</option>
                    <option value="基幹理工学部">基幹理工学部</option>
                    <option value="創造理工学部">創造理工学部</option>
                    <option value="先進理工学部">先進理工学部</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">ステータス</label>
                <select class="form-select" id="filterStatus" onchange="loadMembers()">
                    <option value="">すべて</option>
                    <option value="active">現役</option>
                    <option value="pending">承認待ち</option>
                    <option value="ob_og">OB/OG</option>
                    <option value="withdrawn">退会</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                    リセット
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 集計情報 -->
<div class="row mb-3">
    <div class="col-md-12">
        <span id="memberCount" class="text-muted">読み込み中...</span>
        <span class="ms-3">
            <a href="/index.php?route=members/pending" class="text-decoration-none">
                <span class="badge bg-warning" id="pendingBadge" style="display:none;">承認待ち: <span id="pendingCount">0</span>件</span>
            </a>
        </span>
    </div>
</div>

<!-- 列表示設定 -->
<div class="card mb-2">
    <div class="card-body py-2">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <small class="text-muted me-1">表示列:</small>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_grade" value="grade" checked><label class="form-check-label" for="col_grade">学年</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_faculty" value="faculty" checked><label class="form-check-label" for="col_faculty">学部</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_department" value="department" checked><label class="form-check-label" for="col_department">学科</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_student_id" value="student_id" checked><label class="form-check-label" for="col_student_id">学籍番号</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_status" value="status" checked><label class="form-check-label" for="col_status">ステータス</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_phone" value="phone"><label class="form-check-label" for="col_phone">電話番号</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_email" value="email"><label class="form-check-label" for="col_email">メール</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_line_name" value="line_name"><label class="form-check-label" for="col_line_name">LINE名</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_address" value="address"><label class="form-check-label" for="col_address">住所</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_emergency_contact" value="emergency_contact"><label class="form-check-label" for="col_emergency_contact">緊急連絡先</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_birthdate" value="birthdate"><label class="form-check-label" for="col_birthdate">生年月日</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_allergy" value="allergy"><label class="form-check-label" for="col_allergy">アレルギー</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_sns_allowed" value="sns_allowed"><label class="form-check-label" for="col_sns_allowed">SNS許可</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_sports_registration_no" value="sports_registration_no"><label class="form-check-label" for="col_sports_registration_no">コート予約番号</label></div>
            <div class="form-check form-check-inline mb-0"><input class="form-check-input col-toggle" type="checkbox" id="col_enrollment_year" value="enrollment_year"><label class="form-check-label" for="col_enrollment_year">入学年度</label></div>
        </div>
    </div>
</div>

<!-- 会員一覧テーブル -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-sm" id="memberTable">
            <thead class="table-light">
                <tr>
                    <th style="white-space:nowrap">名前</th>
                    <th class="col-grade" style="white-space:nowrap">学年</th>
                    <th class="col-faculty" style="white-space:nowrap">学部</th>
                    <th class="col-department" style="white-space:nowrap">学科/学系</th>
                    <th class="col-student_id" style="white-space:nowrap">学籍番号</th>
                    <th class="col-status" style="white-space:nowrap">ステータス</th>
                    <th class="col-phone" style="white-space:nowrap">電話番号</th>
                    <th class="col-email" style="white-space:nowrap">メール</th>
                    <th class="col-line_name" style="white-space:nowrap">LINE名</th>
                    <th class="col-address" style="white-space:nowrap">住所</th>
                    <th class="col-emergency_contact" style="white-space:nowrap">緊急連絡先</th>
                    <th class="col-birthdate" style="white-space:nowrap">生年月日</th>
                    <th class="col-allergy" style="white-space:nowrap">アレルギー</th>
                    <th class="col-sns_allowed" style="white-space:nowrap">SNS許可</th>
                    <th class="col-sports_registration_no" style="white-space:nowrap">コート予約番号</th>
                    <th class="col-enrollment_year" style="white-space:nowrap">入学年度</th>
                    <th style="white-space:nowrap" width="80">操作</th>
                </tr>
            </thead>
            <tbody id="memberTableBody">
                <tr>
                    <td colspan="17" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">読み込み中...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ページネーション -->
<nav class="mt-3" id="pagination"></nav>

<!-- 会員編集モーダル -->
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberModalTitle">会員情報編集</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="memberForm">
                    <input type="hidden" id="memberId">

                    <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">名前（漢字）<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nameKanji" required placeholder="山田 太郎">
                            <small class="text-muted">全角スペース区切り</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">名前（カタカナ）<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nameKana" required placeholder="ヤマダ タロウ">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">性別 <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" required>
                                <option value="male">男性</option>
                                <option value="female">女性</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">学年 <span class="text-danger">*</span></label>
                            <select class="form-select" id="grade" required>
                                <option value="1">1年</option>
                                <option value="2">2年</option>
                                <option value="3">3年</option>
                                <option value="4">4年</option>
                    <option value="5">5年</option>
                    <option value="6">6年</option>
                                <option value="M1">M1</option>
                                <option value="M2">M2</option>
                                <option value="OB">OB</option>
                                <option value="OG">OG</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">生年月日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="birthdate" required>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">所属情報</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">学籍番号 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="studentId" required placeholder="" oninput="parseStudentId()">
                            <small class="text-muted" id="studentIdExample">CDあり（例: ）</small>
                        </div>
                        <div class="col-md-6">
                            <div id="studentIdParseResult" class="alert alert-info mb-0 mt-4" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">学部 <span class="text-danger">*</span></label>
                            <select class="form-select" id="faculty" required onchange="updateDepartmentOptions()">
                                <option value="">選択してください</option>
                                <option value="基幹理工学部">基幹理工学部</option>
                                <option value="創造理工学部">創造理工学部</option>
                                <option value="先進理工学部">先進理工学部</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">学科/学系 <span class="text-danger">*</span></label>
                            <select class="form-select" id="department" required onchange="toggleDepartmentOther()">
                                <option value="">選択してください</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="departmentOther" placeholder="学科名を入力" style="display:none;">
                            <small class="text-muted" id="departmentOtherHint" style="display:none;">例: Mathematical Sciences</small>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">連絡先</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">電話番号 <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" required placeholder="090-1234-5678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">緊急連絡先 <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="emergencyContact" required placeholder="03-1234-5678">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">住所 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="address" required placeholder="東京都新宿区西早稲田1-2-3">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">メールアドレス</label>
                            <input type="email" class="form-control" id="email" placeholder="example@email.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">LINE名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lineName" required placeholder="yamada_taro">
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">その他</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">アレルギー</label>
                            <input type="text" class="form-control" id="allergy" placeholder="なし">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">コート予約番号</label>
                            <input type="text" class="form-control" id="sportsRegistrationNo" placeholder="12345678">
                            <small class="text-muted">8桁の番号</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="snsAllowed" checked>
                                <label class="form-check-label" for="snsAllowed">SNSへの写真投稿を許可</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ステータス</label>
                            <select class="form-select" id="status">
                                <option value="pending">承認待ち</option>
                                <option value="active">現役</option>
                                <option value="ob_og">OB/OG</option>
                                <option value="withdrawn">退会</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="deleteMemberBtn" onclick="deleteMember()" style="display:none;">削除</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="saveMember()">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- インポートモーダル -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">会員一括インポート</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Excel/CSVファイルから会員を一括登録します</strong>
                    <p class="mb-0 mt-2">既存の学籍番号と重複する場合は上書きされます。</p>
                </div>

                <div class="mb-3">
                    <label class="form-label">ファイルを選択</label>
                    <input type="file" class="form-control" id="importFile" accept=".xlsx,.xls,.csv">
                </div>

                <h6>必須列（1行目にヘッダー）</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <td>名前（漢字）</td>
                            <td>名前（カタカナ）</td>
                            <td>性別</td>
                            <td>学年</td>
                            <td>学部</td>
                        </tr>
                        <tr>
                            <td>学科</td>
                            <td>学籍番号</td>
                            <td>電話番号</td>
                            <td>住所</td>
                            <td>緊急連絡先</td>
                        </tr>
                        <tr>
                            <td>生年月日</td>
                            <td>アレルギー</td>
                            <td>LINE名</td>
                            <td>SNS投稿可否</td>
                            <td>コート予約番号</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="importMembers()">インポート</button>
            </div>
        </div>
    </div>
</div>


<!-- 新年度作成モーダル -->
<div class="modal fade" id="newYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新年度作成</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    新しい年度を作成します。継続入会や合宿申し込みなどで使用されます。
                </div>

                <div class="mb-3">
                    <label class="form-label">年度 <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="newYear" placeholder="例: 2026" min="2020" max="2100">
                    <small class="text-muted">例: 2026年度（2026年4月～2027年3月）</small>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="setAsCurrent">
                    <label class="form-check-label" for="setAsCurrent">
                        この年度を現在年度に設定する
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="openEnrollment" checked>
                    <label class="form-check-label" for="openEnrollment">
                        入会受付を開始する
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="createNewYear()">作成</button>
            </div>
        </div>
    </div>
</div>


<script>
let memberModal, importModal, newYearModal;
let currentPage = 1;
let searchTimeout;

// 学科/学系のマッピング
const departmentOptions = {
    '基幹理工学部': {
        gakukei_old: ['学系I', '学系II', '学系III'],
        gakukei_new: ['学系I（数学系）', '学系II（工学系）', '学系III（情報系）', '学系IV（メディア系）'],
        departments: ['数学科', '応用数理学科', '機械科学・航空宇宙学科', '電子物理システム学科', '情報理工学科', '情報通信学科', '表現工学科', 'その他（英語学位等）']
    },
    '創造理工学部': {
        departments: ['建築学科', '総合機械工学科', '経営システム工学科', '社会環境工学科', '環境資源工学科', '社会文化領域']
    },
    '先進理工学部': {
        departments: ['物理学科', '応用物理学科', '化学・生命化学科', '応用化学科', '生命医科学科', '電気・情報生命工学科']
    }
};

// 列表示設定をlocalStorageから復元・保存
const COL_STORAGE_KEY = 'memberTableColumns';

function loadColumnSettings() {
    const saved = localStorage.getItem(COL_STORAGE_KEY);
    if (!saved) return;
    const settings = JSON.parse(saved);
    document.querySelectorAll('.col-toggle').forEach(cb => {
        if (cb.value in settings) {
            cb.checked = settings[cb.value];
        }
    });
}

function saveColumnSettings() {
    const settings = {};
    document.querySelectorAll('.col-toggle').forEach(cb => {
        settings[cb.value] = cb.checked;
    });
    localStorage.setItem(COL_STORAGE_KEY, JSON.stringify(settings));
}

function applyColumnVisibility() {
    document.querySelectorAll('.col-toggle').forEach(cb => {
        const visible = cb.checked;
        document.querySelectorAll(`.col-${cb.value}`).forEach(el => {
            el.style.display = visible ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    memberModal = new bootstrap.Modal(document.getElementById('memberModal'));
    importModal = new bootstrap.Modal(document.getElementById('importModal'));
    newYearModal = new bootstrap.Modal(document.getElementById('newYearModal'));

    // 学籍番号の例示を現在年度の下2桁で生成
    const yy = String(new Date().getFullYear()).slice(-2);
    const exId = `1Y${yy}F158-5`;
    document.getElementById('studentId').placeholder = exId;
    document.getElementById('studentIdExample').textContent = `CDあり（例: ${exId}）`;

    // 列設定の復元と適用
    loadColumnSettings();
    applyColumnVisibility();

    // チェックボックス変更時に保存・適用
    document.querySelectorAll('.col-toggle').forEach(cb => {
        cb.addEventListener('change', () => {
            saveColumnSettings();
            applyColumnVisibility();
        });
    });

    loadAcademicYears();
    loadPendingCount();
});

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage = 1;
        loadMembers();
    }, 300);
}

// 日付から現在の年度を計算
function getCurrentAcademicYear() {
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth() + 1; // 0-indexed
    // 1〜3月は前年度、4〜12月は今年度
    return (month >= 4) ? year : year - 1;
}

// URLパラメータから年度を取得
function getYearFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const year = urlParams.get('year');
    return year ? parseInt(year) : null;
}

async function loadAcademicYears() {
    try {
        const res = await fetch('/index.php?route=api/academic-years');
        const data = await res.json();

        if (data.success) {
            const select = document.getElementById('academicYearSelect');
            select.innerHTML = '';

            const currentYear = getCurrentAcademicYear();
            const urlYear = getYearFromUrl(); // URLパラメータから年度を取得
            const targetYear = urlYear || currentYear; // URLパラメータがあればそれを優先

            data.data.years.forEach(year => {
                const option = document.createElement('option');
                option.value = year.year;
                option.textContent = `${year.year}年度${year.year === currentYear ? ' (現在)' : ''}`;
                // URLパラメータまたは現在年度を選択
                if (year.year === targetYear) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            // 年度が読み込まれたら会員一覧を読み込む
            loadMembers();
        }
    } catch (err) {
        console.error(err);
        // エラー時はデフォルトで会員一覧を読み込む
        loadMembers();
    }
}

async function loadMembers() {
    const academicYear = document.getElementById('academicYearSelect').value;

    const params = new URLSearchParams({
        page: currentPage,
        search: document.getElementById('searchQuery').value,
        grade: document.getElementById('filterGrade').value,
        faculty: document.getElementById('filterFaculty').value,
        status: document.getElementById('filterStatus').value
    });

    // 年度パラメータを追加（選択されている場合）
    if (academicYear) {
        params.append('academic_year', academicYear);
    }

    try {
        const res = await fetch(`/index.php?route=api/members&${params}`);
        const data = await res.json();

        if (data.success) {
            renderMembers(data.data.members);
            renderPagination(data.data.pagination);
            document.getElementById('memberCount').textContent = `全 ${data.data.pagination.total} 名`;
        }
    } catch (err) {
        console.error(err);
    }
}

async function loadPendingCount() {
    try {
        const res = await fetch('/index.php?route=api/members/pending/count');
        const data = await res.json();

        if (data.success && data.data.count > 0) {
            document.getElementById('pendingCount').textContent = data.data.count;
            document.getElementById('pendingBadge').style.display = 'inline';
        }
    } catch (err) {
        console.error(err);
    }
}

function renderMembers(members) {
    const tbody = document.getElementById('memberTableBody');

    if (members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="17" class="text-center py-4 text-muted">会員が見つかりません</td></tr>';
        return;
    }

    tbody.innerHTML = members.map(m => `
        <tr>
            <td style="white-space:nowrap">
                <strong>${escapeHtml(m.name_kanji)}</strong>
                <br><small class="text-muted">${escapeHtml(m.name_kana)}</small>
            </td>
            <td class="col-grade">${formatGrade(m.grade, m.gender, m.enrollment_year)}</td>
            <td class="col-faculty" style="white-space:nowrap">${escapeHtml(m.faculty)}</td>
            <td class="col-department" style="white-space:nowrap">${escapeHtml(m.department)}${m.department_not_set ? '<span class="badge bg-warning ms-1">要選択</span>' : ''}</td>
            <td class="col-student_id"><code>${escapeHtml(m.student_id)}</code></td>
            <td class="col-status">${formatStatus(m.status, m.grade, m.gender, m.enrollment_year)}</td>
            <td class="col-phone" style="white-space:nowrap">${escapeHtml(m.phone || '')}</td>
            <td class="col-email">${escapeHtml(m.email || '')}</td>
            <td class="col-line_name">${escapeHtml(m.line_name || '')}</td>
            <td class="col-address">${escapeHtml(m.address || '')}</td>
            <td class="col-emergency_contact" style="white-space:nowrap">${escapeHtml(m.emergency_contact || '')}</td>
            <td class="col-birthdate" style="white-space:nowrap">${escapeHtml(m.birthdate || '')}</td>
            <td class="col-allergy">${escapeHtml(m.allergy || '')}</td>
            <td class="col-sns_allowed" style="text-align:center">${m.sns_allowed ? '○' : '×'}</td>
            <td class="col-sports_registration_no">${escapeHtml(m.sports_registration_no || '')}</td>
            <td class="col-enrollment_year" style="text-align:center">${escapeHtml(m.enrollment_year || '')}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editMember(${m.id})">編集</button>
            </td>
        </tr>
    `).join('');

    applyColumnVisibility();
}

function isRetired(grade, gender, enrollmentYear) {
    if (grade === 'OB' || grade === 'OG') return true;
    if (grade !== '3') return false;
    // enrollment_year があれば入学年+2年後の10月1日を過ぎているか判定
    if (enrollmentYear) {
        const retirementDate = new Date(parseInt(enrollmentYear) + 2, 9, 1); // 10月1日(月は0始まり)
        return new Date() >= retirementDate;
    }
    // enrollment_year がない場合は今の月で判定（フォールバック）
    const month = new Date().getMonth();
    return month >= 9 || month <= 2;
}

function formatGrade(grade, gender, enrollmentYear) {
    if (isRetired(grade, gender, enrollmentYear)) {
        const obog = (grade === 'OG' || gender === 'female') ? 'OG' : 'OB';
        return `<span class="badge bg-secondary">${obog}</span>`;
    }

    const suffix = gender === 'male' ? '男' : '女';
    return `${grade}年${suffix}`;
}

function formatStatus(status, grade, gender, enrollmentYear) {
    if (status === 'active' && isRetired(grade, gender, enrollmentYear)) {
        return '<span class="badge bg-secondary">OB/OG</span>';
    }

    const statusMap = {
        'pending': '<span class="badge bg-warning">承認待ち</span>',
        'active': '<span class="badge bg-success">現役</span>',
        'ob_og': '<span class="badge bg-secondary">OB/OG</span>',
        'withdrawn': '<span class="badge bg-dark">退会</span>'
    };
    return statusMap[status] || status;
}

function renderPagination(pagination) {
    const nav = document.getElementById('pagination');
    if (pagination.total_pages <= 1) {
        nav.innerHTML = '';
        return;
    }

    let html = '<ul class="pagination justify-content-center">';

    // 前へ
    html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${pagination.current_page - 1})">前へ</a>
    </li>`;

    // ページ番号
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === 1 || i === pagination.total_pages || Math.abs(i - pagination.current_page) <= 2) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
            </li>`;
        } else if (Math.abs(i - pagination.current_page) === 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // 次へ
    html += `<li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${pagination.current_page + 1})">次へ</a>
    </li>`;

    html += '</ul>';
    nav.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    loadMembers();
    return false;
}

function resetFilters() {
    document.getElementById('searchQuery').value = '';
    document.getElementById('filterGrade').value = '';
    document.getElementById('filterFaculty').value = '';
    document.getElementById('filterStatus').value = '';
    currentPage = 1;
    loadMembers();
}

function showCreateModal() {
    document.getElementById('memberModalTitle').textContent = '会員を追加';
    document.getElementById('memberId').value = '';
    document.getElementById('memberForm').reset();
    document.getElementById('deleteMemberBtn').style.display = 'none';
    document.getElementById('status').value = 'active';
    document.getElementById('snsAllowed').checked = true;
    document.getElementById('studentIdParseResult').style.display = 'none';
    memberModal.show();
}

async function editMember(id) {
    try {
        const res = await fetch(`/index.php?route=api/members/${id}`);
        const data = await res.json();

        if (data.success) {
            const m = data.data;
            document.getElementById('memberModalTitle').textContent = '会員情報編集';
            document.getElementById('memberId').value = m.id;
            document.getElementById('nameKanji').value = m.name_kanji;
            document.getElementById('nameKana').value = m.name_kana;
            document.getElementById('gender').value = m.gender;
            document.getElementById('grade').value = m.grade;
            document.getElementById('birthdate').value = m.birthdate;
            document.getElementById('studentId').value = m.student_id;
            document.getElementById('faculty').value = m.faculty;
            updateDepartmentOptions();

            // 学科/学系の設定（リストにない場合は「その他」扱い）
            const deptSelect = document.getElementById('department');
            const deptOptions = Array.from(deptSelect.options).map(opt => opt.value);

            if (deptOptions.includes(m.department)) {
                // リストに存在する場合
                deptSelect.value = m.department;
            } else if (m.department) {
                // リストにない場合は「その他」を選択して自由記述欄に入力
                deptSelect.value = 'その他（英語学位等）';
                document.getElementById('departmentOther').value = m.department;
                toggleDepartmentOther();
            }

            document.getElementById('phone').value = m.phone;
            document.getElementById('emergencyContact').value = m.emergency_contact;
            document.getElementById('address').value = m.address;
            document.getElementById('email').value = m.email || '';
            document.getElementById('lineName').value = m.line_name;
            document.getElementById('allergy').value = m.allergy || '';
            document.getElementById('sportsRegistrationNo').value = m.sports_registration_no || '';
            document.getElementById('snsAllowed').checked = m.sns_allowed == 1;
            document.getElementById('status').value = m.status;
            document.getElementById('deleteMemberBtn').style.display = 'block';
            document.getElementById('studentIdParseResult').style.display = 'none';
            memberModal.show();
        }
    } catch (err) {
        alert('会員情報の取得に失敗しました');
    }
}

function updateDepartmentOptions() {
    const faculty = document.getElementById('faculty').value;
    const grade = document.getElementById('grade').value;
    const deptSelect = document.getElementById('department');

    deptSelect.innerHTML = '<option value="">選択してください</option>';

    if (!faculty || !departmentOptions[faculty]) return;

    const opts = departmentOptions[faculty];

    if (faculty === '基幹理工学部') {
        // 基幹理工学部の場合、学年によって選択肢が異なる
        if (grade === '1') {
            // 1年生は学系を選択（入学年度で判断）
            const studentId = document.getElementById('studentId').value;
            const yearMatch = studentId.match(/^1W(\d{2})/);
            const enrollmentYear = yearMatch ? 2000 + parseInt(yearMatch[1]) : new Date().getFullYear();

            const gakukeiList = enrollmentYear >= 2025 ? opts.gakukei_new : opts.gakukei_old;
            gakukeiList.forEach(g => {
                deptSelect.innerHTML += `<option value="${g}">${g}</option>`;
            });
        } else {
            // 2年生以上は学科を選択
            opts.departments.forEach(d => {
                deptSelect.innerHTML += `<option value="${d}">${d}</option>`;
            });
        }
    } else {
        // 創造・先進は学科のみ
        opts.departments.forEach(d => {
            deptSelect.innerHTML += `<option value="${d}">${d}</option>`;
        });
    }
}

function toggleDepartmentOther() {
    const deptSelect = document.getElementById('department');
    const otherInput = document.getElementById('departmentOther');
    const otherHint = document.getElementById('departmentOtherHint');

    if (deptSelect.value === 'その他（英語学位等）') {
        otherInput.style.display = 'block';
        otherHint.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherHint.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
}

async function parseStudentId() {
    const studentId = document.getElementById('studentId').value;
    if (studentId.length < 5) {
        document.getElementById('studentIdParseResult').style.display = 'none';
        return;
    }

    try {
        const res = await fetch(`/index.php?route=api/members/parse-student-id&student_id=${encodeURIComponent(studentId)}`);
        const data = await res.json();

        const resultDiv = document.getElementById('studentIdParseResult');

        if (data.success && data.data.is_valid) {
            const d = data.data;
            resultDiv.innerHTML = `
                <small>
                    <strong>自動判定:</strong> ${escapeHtml(d.faculty)} ${d.enrollment_year}年入学
                    ${d.department ? `<br>学科: ${escapeHtml(d.department)}` : ''}
                </small>
            `;
            resultDiv.className = 'alert alert-success mb-0 mt-2 py-2';
            resultDiv.style.display = 'block';

            // フォームに自動入力
            document.getElementById('faculty').value = d.faculty;
            updateDepartmentOptions();
            if (d.department) {
                document.getElementById('department').value = d.department;
            }
        } else {
            resultDiv.innerHTML = '<small>学籍番号の形式が正しくありません</small>';
            resultDiv.className = 'alert alert-warning mb-0 mt-2 py-2';
            resultDiv.style.display = 'block';
        }
    } catch (err) {
        console.error(err);
    }
}

async function saveMember() {
    const id = document.getElementById('memberId').value;

    // 学科/学系の取得（「その他」の場合は自由記述を使用）
    let department = document.getElementById('department').value;
    if (department === 'その他（英語学位等）') {
        const otherValue = document.getElementById('departmentOther').value.trim();
        if (!otherValue) {
            alert('学科名を入力してください');
            return;
        }
        department = otherValue;
    }

    const data = {
        name_kanji: document.getElementById('nameKanji').value,
        name_kana: document.getElementById('nameKana').value,
        gender: document.getElementById('gender').value,
        grade: document.getElementById('grade').value,
        birthdate: document.getElementById('birthdate').value,
        student_id: document.getElementById('studentId').value,
        faculty: document.getElementById('faculty').value,
        department: department,
        phone: document.getElementById('phone').value,
        emergency_contact: document.getElementById('emergencyContact').value,
        address: document.getElementById('address').value,
        email: document.getElementById('email').value,
        line_name: document.getElementById('lineName').value,
        allergy: document.getElementById('allergy').value,
        sports_registration_no: document.getElementById('sportsRegistrationNo').value,
        sns_allowed: document.getElementById('snsAllowed').checked ? 1 : 0,
        status: document.getElementById('status').value
    };

    const url = id ? `/index.php?route=api/members/${id}` : '/index.php?route=api/members';
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            memberModal.hide();
            loadMembers();
            showToast('保存しました');
        } else {
            alert(result.error?.message || '保存に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

async function deleteMember() {
    const id = document.getElementById('memberId').value;
    if (!confirm('この会員を削除しますか？\n\n※関連する申し込みデータも削除されます。')) return;

    try {
        const res = await fetch(`/index.php?route=api/members/${id}`, {
            method: 'DELETE'
        });

        const result = await res.json();

        if (result.success) {
            memberModal.hide();
            loadMembers();
            showToast('削除しました');
        } else {
            alert(result.error?.message || '削除に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function showImportModal() {
    document.getElementById('importFile').value = '';
    importModal.show();
}

async function importMembers() {
    const fileInput = document.getElementById('importFile');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('ファイルを選択してください');
        return;
    }

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    try {
        const res = await fetch('/index.php?route=api/members/import', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (result.success) {
            importModal.hide();
            loadMembers();
            showToast(`${result.data.imported}名をインポートしました`);
        } else {
            alert(result.error?.message || 'インポートに失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function showNewYearModal() {
    // 現在の年度を取得して+1を提案
    const currentSelect = document.getElementById('academicYearSelect');
    const currentYear = parseInt(currentSelect.value) || new Date().getFullYear();
    document.getElementById('newYear').value = currentYear + 1;

    newYearModal.show();
}

async function createNewYear() {
    const year = parseInt(document.getElementById('newYear').value);
    const setAsCurrent = document.getElementById('setAsCurrent').checked;
    const openEnrollment = document.getElementById('openEnrollment').checked;

    if (!year || year < 2020 || year > 2100) {
        alert('年度は2020～2100の範囲で指定してください');
        return;
    }

    try {
        const res = await fetch('/index.php?route=api/academic-years', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                year: year,
                is_current: setAsCurrent ? 1 : 0,
                enrollment_open: openEnrollment ? 1 : 0
            })
        });

        const result = await res.json();

        if (result.success) {
            newYearModal.hide();
            loadAcademicYears();
            showToast(`${year}年度を作成しました`);
        } else {
            alert(result.error?.message || '年度の作成に失敗しました');
        }
    } catch (err) {
        alert('通信エラーが発生しました');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message) {
    // トースト通知（簡易実装）
    alert(message);
}


</script>
