<?php
$pageTitle = htmlspecialchars($camp['name']) . ' - 日程選択';
$appName = '合宿申し込み';
$totalDays = $camp['nights'] + 1;

ob_start();
?>

<div class="card shadow">
    <div class="card-body p-4">
        <h2 class="text-center mb-4"><?= htmlspecialchars($camp['name']) ?></h2>

        <h5 class="mb-3">参加日程を選択してください</h5>

        <form id="scheduleForm" onsubmit="return handleSubmit(event)">
            <input type="hidden" name="member_id" value="<?= $member['id'] ?>">

            <!-- 参加パターン -->
            <div class="mb-4">
                <label class="form-label fw-bold">参加パターン</label>
                <div class="list-group">
                    <label class="list-group-item">
                        <input class="form-check-input me-2" type="radio" name="pattern" value="full" checked onchange="updatePattern()">
                        全日程参加（1日目〜<?= $totalDays ?>日目）
                    </label>
                    <label class="list-group-item">
                        <input class="form-check-input me-2" type="radio" name="pattern" value="late" onchange="updatePattern()">
                        途中参加（遅れて参加する）
                    </label>
                    <label class="list-group-item">
                        <input class="form-check-input me-2" type="radio" name="pattern" value="early" onchange="updatePattern()">
                        途中抜け（早めに帰る）
                    </label>
                    <label class="list-group-item">
                        <input class="form-check-input me-2" type="radio" name="pattern" value="both" onchange="updatePattern()">
                        途中参加 & 途中抜け
                    </label>
                </div>
            </div>

            <!-- 詳細日程 -->
            <div id="detailsSection" class="mb-4 d-none">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6" id="joinSection">
                                <label class="form-label fw-bold">参加開始</label>
                                <div class="input-group mb-2">
                                    <select class="form-select" name="join_day" id="joinDay" onchange="updateJoinTimingOptions()">
                                        <?php for ($i = 1; $i <= $totalDays; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?>日目</option>
                                        <?php endfor; ?>
                                    </select>
                                    <span class="input-group-text">の</span>
                                </div>
                                <select class="form-select" name="join_timing" id="joinTiming">
                                    <!-- JavaScriptで動的に生成 -->
                                </select>
                            </div>

                            <div class="col-md-6" id="leaveSection">
                                <label class="form-label fw-bold">参加終了</label>
                                <div class="input-group mb-2">
                                    <select class="form-select" name="leave_day" id="leaveDay" onchange="updateLeaveTimingOptions()">
                                        <?php for ($i = 1; $i <= $totalDays; $i++): ?>
                                        <option value="<?= $i ?>" <?= $i === $totalDays ? 'selected' : '' ?>><?= $i ?>日目</option>
                                        <?php endfor; ?>
                                    </select>
                                    <span class="input-group-text">の</span>
                                </div>
                                <select class="form-select" name="leave_timing" id="leaveTiming">
                                    <!-- JavaScriptで動的に生成 -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 交通手段 -->
            <div class="mb-4">
                <label class="form-label fw-bold">交通手段</label>
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="form-check mb-2" id="outboundBusSection">
                            <input class="form-check-input" type="checkbox" name="use_outbound_bus" id="useOutboundBus" value="1" checked>
                            <label class="form-check-label" for="useOutboundBus">
                                往路バスを利用する
                            </label>
                        </div>
                        <div class="form-check" id="returnBusSection">
                            <input class="form-check-input" type="checkbox" name="use_return_bus" id="useReturnBus" value="1" checked>
                            <label class="form-check-label" for="useReturnBus">
                                復路バスを利用する
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                    <i class="bi bi-arrow-left"></i> 戻る
                </button>
                <button type="submit" class="btn btn-primary flex-grow-1">
                    確認画面へ <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const TOTAL_DAYS = <?= $totalDays ?>;

// 参加開始タイミングの選択肢を更新（管理システムと完全に同じロジック）
function updateJoinTimingOptions() {
    const joinDay = parseInt(document.getElementById('joinDay').value);
    const joinTimingSelect = document.getElementById('joinTiming');
    const currentValue = joinTimingSelect.value;

    let options = [];

    // 1日目は往路バス到着後なので、朝食・午前イベント・昼食はない
    if (joinDay === 1) {
        options.push({ value: 'outbound_bus', label: '往路バスから' });
        options.push({ value: 'afternoon', label: '午後イベントから' });   // 昼食を食べずに午後イベントから参加
        options.push({ value: 'dinner', label: '夕食から' });              // 夕食から参加
        options.push({ value: 'night', label: '夜から' });                 // 夕食を食べずに夜イベントから参加
        options.push({ value: 'lodging', label: '宿泊から' });             // 宿泊のみ参加
    } else {
        // 2日目以降は朝食からの選択肢も表示
        options.push({ value: 'breakfast', label: '朝食から' });           // 朝食から参加
        options.push({ value: 'morning', label: '午前イベントから' });     // 朝食を食べずに午前イベントから参加
        options.push({ value: 'lunch', label: '昼食から' });               // 昼食から参加
        options.push({ value: 'afternoon', label: '午後イベントから' });   // 昼食を食べずに午後イベントから参加
        options.push({ value: 'dinner', label: '夕食から' });              // 夕食から参加
        options.push({ value: 'night', label: '夜から' });                 // 夕食を食べずに夜イベントから参加
        options.push({ value: 'lodging', label: '宿泊から' });             // 宿泊のみ参加
    }

    // セレクトボックスを更新
    joinTimingSelect.innerHTML = options.map(opt =>
        `<option value="${opt.value}">${opt.label}</option>`
    ).join('');

    // 可能であれば以前の値を復元
    const validValues = options.map(opt => opt.value);
    if (validValues.includes(currentValue)) {
        joinTimingSelect.value = currentValue;
    } else if (joinDay === 1) {
        joinTimingSelect.value = 'outbound_bus';
    } else {
        joinTimingSelect.value = 'breakfast';
    }

    updateBusFromTiming();
}

// 参加終了タイミングの選択肢を更新（管理システムと完全に同じロジック）
function updateLeaveTimingOptions() {
    const leaveDay = parseInt(document.getElementById('leaveDay').value);
    const leaveTimingSelect = document.getElementById('leaveTiming');
    const currentValue = leaveTimingSelect.value;

    let options = [];

    // 共通の選択肢
    options.push({ value: 'before_breakfast', label: '朝食前まで' });  // 朝食を食べずに帰る
    options.push({ value: 'breakfast', label: '朝食まで' });           // 朝食を食べて午前イベントに参加せず帰る
    options.push({ value: 'morning', label: '午前イベントまで' });     // 午前イベントに参加して昼食を食べずに帰る
    options.push({ value: 'lunch', label: '昼食まで' });               // 昼食を食べて午後イベントに参加せず帰る

    // 最終日以外のみ午後イベント・夕食・夜を表示（最終日は昼食後バスで帰るため）
    if (leaveDay < TOTAL_DAYS) {
        options.push({ value: 'afternoon', label: '午後イベントまで' });   // 午後イベントに参加して夕食を食べずに帰る
        options.push({ value: 'dinner', label: '夕食まで' });              // 夕食を食べて夜イベントに参加せず帰る
        options.push({ value: 'night', label: '夜まで' });                 // 夜イベントに参加して宿泊せずに帰る
    }

    // 最終日のみ「復路バスまで」を表示
    if (leaveDay === TOTAL_DAYS) {
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
    } else if (leaveDay === TOTAL_DAYS) {
        leaveTimingSelect.value = 'return_bus';
    } else {
        leaveTimingSelect.value = 'night';
    }

    updateBusFromTiming();
}

// タイミング変更時にバス使用を自動設定
function updateBusFromTiming() {
    const pattern = document.querySelector('input[name="pattern"]:checked').value;
    const joinDay = parseInt(document.getElementById('joinDay').value);
    const joinTiming = document.getElementById('joinTiming').value;
    const leaveDay = parseInt(document.getElementById('leaveDay').value);
    const leaveTiming = document.getElementById('leaveTiming').value;

    const outboundBusSection = document.getElementById('outboundBusSection');
    const returnBusSection = document.getElementById('returnBusSection');
    const useOutboundBus = document.getElementById('useOutboundBus');
    const useReturnBus = document.getElementById('useReturnBus');

    // 往路バス: 1日目で「往路バスから」を選択した場合のみ選択可能
    const canUseOutbound = (pattern === 'full') || (joinDay === 1 && joinTiming === 'outbound_bus');
    if (canUseOutbound) {
        outboundBusSection.classList.remove('d-none');
        // 往路バスから参加なら自動でチェック
        if (joinDay === 1 && joinTiming === 'outbound_bus') {
            useOutboundBus.checked = true;
        }
    } else {
        outboundBusSection.classList.add('d-none');
        useOutboundBus.checked = false;
    }

    // 復路バス: 最終日で「復路バスまで」を選択した場合のみ選択可能
    const canUseReturn = (pattern === 'full') || (leaveDay === TOTAL_DAYS && leaveTiming === 'return_bus');
    if (canUseReturn) {
        returnBusSection.classList.remove('d-none');
        // 復路バスまで参加なら自動でチェック
        if (leaveDay === TOTAL_DAYS && leaveTiming === 'return_bus') {
            useReturnBus.checked = true;
        }
    } else {
        returnBusSection.classList.add('d-none');
        useReturnBus.checked = false;
    }
}

function updatePattern() {
    const pattern = document.querySelector('input[name="pattern"]:checked').value;
    const detailsSection = document.getElementById('detailsSection');
    const joinSection = document.getElementById('joinSection');
    const leaveSection = document.getElementById('leaveSection');
    const useOutboundBus = document.getElementById('useOutboundBus');
    const useReturnBus = document.getElementById('useReturnBus');

    if (pattern === 'full') {
        detailsSection.classList.add('d-none');
        document.getElementById('joinDay').value = 1;
        document.getElementById('leaveDay').value = TOTAL_DAYS;
        updateJoinTimingOptions();
        updateLeaveTimingOptions();
        document.getElementById('joinTiming').value = 'outbound_bus';
        document.getElementById('leaveTiming').value = 'return_bus';
        useOutboundBus.checked = true;
        useReturnBus.checked = true;
    } else {
        detailsSection.classList.remove('d-none');

        if (pattern === 'late') {
            joinSection.classList.remove('d-none');
            leaveSection.classList.add('d-none');
            document.getElementById('leaveDay').value = TOTAL_DAYS;
            updateLeaveTimingOptions();
            document.getElementById('leaveTiming').value = 'return_bus';
            // 途中参加のデフォルトは2日目
            if (document.getElementById('joinDay').value === '1') {
                document.getElementById('joinDay').value = Math.min(2, TOTAL_DAYS);
            }
            updateJoinTimingOptions();
            useReturnBus.checked = true;
        } else if (pattern === 'early') {
            joinSection.classList.add('d-none');
            leaveSection.classList.remove('d-none');
            document.getElementById('joinDay').value = 1;
            updateJoinTimingOptions();
            document.getElementById('joinTiming').value = 'outbound_bus';
            // 途中抜けのデフォルトは最終日-1（ただし最低1日目）
            if (document.getElementById('leaveDay').value === String(TOTAL_DAYS)) {
                document.getElementById('leaveDay').value = Math.max(1, TOTAL_DAYS - 1);
            }
            updateLeaveTimingOptions();
            useOutboundBus.checked = true;
        } else if (pattern === 'both') {
            joinSection.classList.remove('d-none');
            leaveSection.classList.remove('d-none');
            // 途中参加のデフォルトは2日目
            if (document.getElementById('joinDay').value === '1') {
                document.getElementById('joinDay').value = Math.min(2, TOTAL_DAYS);
            }
            // 途中抜けのデフォルトは最終日-1
            if (document.getElementById('leaveDay').value === String(TOTAL_DAYS)) {
                document.getElementById('leaveDay').value = Math.max(1, TOTAL_DAYS - 1);
            }
            updateJoinTimingOptions();
            updateLeaveTimingOptions();
        }
    }

    updateBusFromTiming();
}

// タイミング変更時にもバスオプションを更新
document.getElementById('joinTiming').addEventListener('change', updateBusFromTiming);
document.getElementById('leaveTiming').addEventListener('change', updateBusFromTiming);

function handleSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }

    window.location.href = `/apply/<?= $token ?>/review?${params.toString()}`;
    return false;
}

// 初期化
document.addEventListener('DOMContentLoaded', function() {
    updateJoinTimingOptions();
    updateLeaveTimingOptions();
    updateBusFromTiming();
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/public.php';
?>
