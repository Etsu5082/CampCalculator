<?php
/**
 * 計算デバッグスクリプト
 *
 * このスクリプトをブラウザで実行すると、計算ロジックの詳細をデバッグできます。
 * URL: /debug_calculation.php?camp_id=X
 */

define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/Camp.php';
require_once SRC_PATH . '/Models/Participant.php';
require_once SRC_PATH . '/Models/TimeSlot.php';
require_once SRC_PATH . '/Models/Expense.php';
require_once SRC_PATH . '/Services/CalculationService.php';

header('Content-Type: text/html; charset=utf-8');

$campId = isset($_GET['camp_id']) ? (int)$_GET['camp_id'] : 0;

if ($campId === 0) {
    // 合宿一覧を表示
    $db = Database::getInstance();
    $camps = $db->fetchAll("SELECT id, name, nights, court_fee_per_unit, gym_fee_per_unit, banquet_fee_per_person FROM camps ORDER BY id DESC");

    echo "<h1>合宿一覧</h1>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>名前</th><th>泊数</th><th>コート単価</th><th>体育館単価</th><th>宴会場単価</th><th>操作</th></tr>";
    foreach ($camps as $camp) {
        echo "<tr>";
        echo "<td>{$camp['id']}</td>";
        echo "<td>{$camp['name']}</td>";
        echo "<td>{$camp['nights']}</td>";
        echo "<td>¥" . number_format($camp['court_fee_per_unit'] ?? 0) . "</td>";
        echo "<td>¥" . number_format($camp['gym_fee_per_unit'] ?? 0) . "</td>";
        echo "<td>¥" . number_format($camp['banquet_fee_per_person'] ?? 0) . "</td>";
        echo "<td><a href='?camp_id={$camp['id']}'>詳細</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

$db = Database::getInstance();

// 合宿情報
$camp = $db->fetch("SELECT * FROM camps WHERE id = ?", [$campId]);
if (!$camp) {
    die("合宿が見つかりません");
}

echo "<h1>{$camp['name']} デバッグ</h1>";

// 基本情報
echo "<h2>1. 合宿基本情報</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>項目</th><th>値</th></tr>";
echo "<tr><td>宿泊費/泊</td><td>¥" . number_format($camp['lodging_fee_per_night']) . "</td></tr>";
echo "<tr><td>保険料</td><td>¥" . number_format($camp['insurance_fee']) . "</td></tr>";
echo "<tr><td>コート単価/面</td><td>¥" . number_format($camp['court_fee_per_unit'] ?? 0) . "</td></tr>";
echo "<tr><td>体育館単価/コマ</td><td>¥" . number_format($camp['gym_fee_per_unit'] ?? 0) . "</td></tr>";
echo "<tr><td>宴会場単価/人</td><td>¥" . number_format($camp['banquet_fee_per_person'] ?? 0) . "</td></tr>";
echo "<tr><td>バス代往復</td><td>¥" . number_format($camp['bus_fee_round_trip'] ?? 0) . "</td></tr>";
echo "<tr><td>バス代別設定</td><td>" . ($camp['bus_fee_separate'] ? '有効' : '無効') . "</td></tr>";
echo "<tr><td>往路バス代</td><td>¥" . number_format($camp['bus_fee_outbound'] ?? 0) . "</td></tr>";
echo "<tr><td>復路バス代</td><td>¥" . number_format($camp['bus_fee_return'] ?? 0) . "</td></tr>";
echo "<tr><td>往路高速代</td><td>¥" . number_format($camp['highway_fee_outbound'] ?? 0) . "</td></tr>";
echo "<tr><td>復路高速代</td><td>¥" . number_format($camp['highway_fee_return'] ?? 0) . "</td></tr>";
echo "<tr><td>レンタカー有効</td><td>" . ($camp['use_rental_car'] ? '有効' : '無効') . "</td></tr>";
echo "<tr><td>レンタカー代</td><td>¥" . number_format($camp['rental_car_fee'] ?? 0) . "</td></tr>";
echo "<tr><td>レンタカー高速代</td><td>¥" . number_format($camp['rental_car_highway_fee'] ?? 0) . "</td></tr>";
echo "</table>";

// タイムスロット
echo "<h2>2. タイムスロット</h2>";
$slots = $db->fetchAll("SELECT * FROM time_slots WHERE camp_id = ? ORDER BY day_number, FIELD(slot_type, 'outbound', 'morning', 'afternoon', 'banquet', 'return')", [$campId]);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>日</th><th>種別</th><th>活動種別</th><th>施設料金</th><th>コート数</th><th>説明</th><th style='background:yellow'>問題</th></tr>";
foreach ($slots as $slot) {
    $problem = '';
    // 問題チェック
    if ($slot['activity_type'] === 'tennis' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
        $expected = ($camp['court_fee_per_unit'] ?? 0) * ($slot['court_count'] ?? 1);
        $problem = "⚠️ facility_fee=0 (期待値: ¥" . number_format($expected) . ")";
    }
    if ($slot['activity_type'] === 'gym' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
        $expected = $camp['gym_fee_per_unit'] ?? 0;
        $problem = "⚠️ facility_fee=0 (期待値: ¥" . number_format($expected) . ")";
    }
    if ($slot['activity_type'] === 'banquet' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
        $expected = $camp['banquet_fee_per_person'] ?? 0;
        $problem = "⚠️ facility_fee=0 (期待値: ¥" . number_format($expected) . ")";
    }

    echo "<tr>";
    echo "<td>{$slot['id']}</td>";
    echo "<td>{$slot['day_number']}</td>";
    echo "<td>{$slot['slot_type']}</td>";
    echo "<td>{$slot['activity_type']}</td>";
    echo "<td style='" . ($problem ? 'background:red;color:white' : '') . "'>¥" . number_format($slot['facility_fee'] ?? 0) . "</td>";
    echo "<td>{$slot['court_count']}</td>";
    echo "<td>{$slot['description']}</td>";
    echo "<td style='background:" . ($problem ? 'yellow' : 'lightgreen') . "'>" . ($problem ?: '✓ OK') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 参加者
echo "<h2>3. 参加者</h2>";
$participants = $db->fetchAll("SELECT * FROM participants WHERE camp_id = ? ORDER BY name", [$campId]);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>名前</th><th>参加日</th><th>離脱日</th><th>往路バス</th><th>復路バス</th><th>レンタカー</th></tr>";
foreach ($participants as $p) {
    echo "<tr>";
    echo "<td>{$p['id']}</td>";
    echo "<td>{$p['name']}</td>";
    echo "<td>{$p['join_day']}日目({$p['join_timing']})</td>";
    echo "<td>{$p['leave_day']}日目({$p['leave_timing']})</td>";
    echo "<td>" . ($p['use_outbound_bus'] ? '○' : '×') . "</td>";
    echo "<td>" . ($p['use_return_bus'] ? '○' : '×') . "</td>";
    echo "<td>" . ($p['use_rental_car'] ? '○' : '×') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 参加者スロット
echo "<h2>4. 参加者スロット (participant_slots)</h2>";
foreach ($participants as $p) {
    $pSlots = $db->fetchAll(
        "SELECT ps.*, ts.day_number, ts.slot_type, ts.activity_type, ts.facility_fee
         FROM participant_slots ps
         JOIN time_slots ts ON ps.time_slot_id = ts.id
         WHERE ps.participant_id = ?
         ORDER BY ts.day_number",
        [$p['id']]
    );

    echo "<h4>{$p['name']} (ID: {$p['id']})</h4>";
    echo "<table border='1' cellpadding='3'>";
    echo "<tr><th>スロットID</th><th>日</th><th>種別</th><th>活動</th><th>施設料金</th><th>参加</th></tr>";
    foreach ($pSlots as $ps) {
        $attending = $ps['is_attending'] ? '✓参加' : '×不参加';
        $bgColor = $ps['is_attending'] ? 'lightgreen' : 'lightgray';
        echo "<tr style='background:{$bgColor}'>";
        echo "<td>{$ps['time_slot_id']}</td>";
        echo "<td>{$ps['day_number']}</td>";
        echo "<td>{$ps['slot_type']}</td>";
        echo "<td>{$ps['activity_type']}</td>";
        echo "<td>¥" . number_format($ps['facility_fee'] ?? 0) . "</td>";
        echo "<td>{$attending}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 雑費
echo "<h2>5. 雑費</h2>";
$expenses = $db->fetchAll("SELECT * FROM expenses WHERE camp_id = ?", [$campId]);
if (empty($expenses)) {
    echo "<p>雑費なし</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>名前</th><th>金額</th><th>対象</th><th>対象日</th><th>対象スロット</th></tr>";
    foreach ($expenses as $e) {
        echo "<tr>";
        echo "<td>{$e['id']}</td>";
        echo "<td>{$e['name']}</td>";
        echo "<td>¥" . number_format($e['amount']) . "</td>";
        echo "<td>{$e['target_type']}</td>";
        echo "<td>{$e['target_day']}</td>";
        echo "<td>{$e['target_slot']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 計算結果
echo "<h2>6. 計算結果</h2>";
try {
    $service = new CalculationService();
    $result = $service->calculate($campId);

    echo "<h3>サマリー</h3>";
    echo "<p>総費用: ¥" . number_format($result['summary']['total_amount']) . "</p>";
    echo "<p>参加者数: " . $result['summary']['participant_count'] . "人</p>";
    echo "<p>平均負担額: ¥" . number_format($result['summary']['average_amount']) . "</p>";

    echo "<h3>参加者別内訳</h3>";
    foreach ($result['participants'] as $p) {
        echo "<h4>{$p['name']} - 合計: ¥" . number_format($p['total']) . "</h4>";
        echo "<table border='1' cellpadding='3'>";
        echo "<tr><th>カテゴリ</th><th>項目</th><th>金額</th></tr>";
        foreach ($p['items'] as $item) {
            $color = '';
            if ($item['category'] === 'facility') $color = 'background:lightyellow';
            if ($item['amount'] < 0) $color = 'background:lightcoral';
            echo "<tr style='{$color}'>";
            echo "<td>{$item['category']}</td>";
            echo "<td>{$item['name']}</td>";
            echo "<td>¥" . number_format($item['amount']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>計算エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 修正用SQL生成
echo "<h2>7. 修正用SQL（施設料金が0のスロットを修正）</h2>";
$fixSqls = [];
foreach ($slots as $slot) {
    if ($slot['activity_type'] === 'tennis' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
        $expected = ($camp['court_fee_per_unit'] ?? 0) * ($slot['court_count'] ?? 1);
        if ($expected > 0) {
            $fixSqls[] = "UPDATE time_slots SET facility_fee = {$expected} WHERE id = {$slot['id']};";
        }
    }
    if ($slot['activity_type'] === 'gym' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
        $expected = $camp['gym_fee_per_unit'] ?? 0;
        if ($expected > 0) {
            $fixSqls[] = "UPDATE time_slots SET facility_fee = {$expected} WHERE id = {$slot['id']};";
        }
    }
    if ($slot['activity_type'] === 'banquet' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
        $expected = $camp['banquet_fee_per_person'] ?? 0;
        if ($expected > 0) {
            $fixSqls[] = "UPDATE time_slots SET facility_fee = {$expected} WHERE id = {$slot['id']};";
        }
    }
}

if (empty($fixSqls)) {
    echo "<p style='color:green'>修正が必要なスロットはありません</p>";
} else {
    echo "<p style='color:red'>以下のSQLを実行して施設料金を修正してください：</p>";
    echo "<pre style='background:#f0f0f0;padding:10px'>";
    echo htmlspecialchars(implode("\n", $fixSqls));
    echo "</pre>";

    // 自動修正リンク
    echo "<form method='POST' action='?camp_id={$campId}&action=fix'>";
    echo "<button type='submit' onclick=\"return confirm('施設料金を自動修正しますか？');\">自動修正を実行</button>";
    echo "</form>";
}

// 自動修正処理
if (isset($_GET['action']) && $_GET['action'] === 'fix' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>自動修正実行結果</h3>";
    $fixed = 0;
    foreach ($slots as $slot) {
        $expected = 0;
        if ($slot['activity_type'] === 'tennis' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
            $expected = ($camp['court_fee_per_unit'] ?? 0) * ($slot['court_count'] ?? 1);
        }
        if ($slot['activity_type'] === 'gym' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
            $expected = $camp['gym_fee_per_unit'] ?? 0;
        }
        if ($slot['activity_type'] === 'banquet' && ($slot['facility_fee'] === null || $slot['facility_fee'] == 0)) {
            $expected = $camp['banquet_fee_per_person'] ?? 0;
        }

        if ($expected > 0) {
            $db->execute("UPDATE time_slots SET facility_fee = ? WHERE id = ?", [$expected, $slot['id']]);
            echo "<p>スロットID {$slot['id']} を ¥" . number_format($expected) . " に修正しました</p>";
            $fixed++;
        }
    }

    if ($fixed > 0) {
        echo "<p style='color:green'>{$fixed}件のスロットを修正しました。<a href='?camp_id={$campId}'>ページを再読み込み</a>してください。</p>";
    }
}

echo "<hr>";
echo "<p><a href='?'>← 合宿一覧に戻る</a></p>";
