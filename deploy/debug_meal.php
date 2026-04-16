<?php
/**
 * 食事調整計算デバッグページ
 * ブラウザで http://your-domain/debug_meal.php?id=<participant_id> にアクセス
 */

// エラー表示
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 定数定義
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('SRC_PATH', BASE_PATH . '/src');

// オートロード
spl_autoload_register(function ($class) {
    $paths = [
        SRC_PATH . '/Core/',
        SRC_PATH . '/Controllers/',
        SRC_PATH . '/Models/',
        SRC_PATH . '/Services/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once CONFIG_PATH . '/database.php';

// HTML出力開始
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>食事調整計算デバッグ</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #666;
            margin-top: 30px;
            border-left: 4px solid #2196F3;
            padding-left: 10px;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success-box {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .error-box {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .night-section {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
        }
        .meal-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .meal-item:last-child {
            border-bottom: none;
        }
        .adjustment {
            font-weight: bold;
            color: #4CAF50;
        }
        .adjustment.remove {
            color: #f44336;
        }
        .total {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-align: right;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 4px;
            margin: 20px 0;
        }
        .participant-list {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .participant-list a {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .participant-list a:hover {
            background: #1976D2;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 食事調整計算デバッグ</h1>

<?php
try {
    $db = Database::getInstance();
    $participantModel = new Participant();
    $campModel = new Camp();

    // 参加者ID取得
    $participantId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$participantId) {
        // 参加者IDが指定されていない場合、選択肢を表示
        echo '<div class="info-box">';
        echo '<strong>参加者を選択してください</strong><br>';
        echo 'URLに <code>?id=参加者ID</code> を追加するか、下記から選択してください。';
        echo '</div>';

        // 2日目午前～3日目午後参加の参加者を検索
        $participants = $db->fetchAll(
            "SELECT p.id, p.name, c.name as camp_name, p.join_day, p.join_timing, p.leave_day, p.leave_timing
             FROM participants p
             JOIN camps c ON p.camp_id = c.id
             WHERE p.join_day = 2 AND p.join_timing = 'morning'
             AND p.leave_day = 3 AND p.leave_timing = 'afternoon'
             ORDER BY p.id DESC
             LIMIT 20"
        );

        if (!empty($participants)) {
            echo '<h2>2日目午前～3日目午後参加の参加者</h2>';
            echo '<div class="participant-list">';
            foreach ($participants as $p) {
                echo "<a href='?id={$p['id']}'>{$p['name']} (ID: {$p['id']}) - {$p['camp_name']}</a>";
            }
            echo '</div>';
        }

        // 最近の参加者を表示
        $recentParticipants = $db->fetchAll(
            "SELECT p.id, p.name, c.name as camp_name, p.join_day, p.join_timing, p.leave_day, p.leave_timing
             FROM participants p
             JOIN camps c ON p.camp_id = c.id
             ORDER BY p.id DESC
             LIMIT 20"
        );

        echo '<h2>最近の参加者</h2>';
        echo '<div class="participant-list">';
        foreach ($recentParticipants as $p) {
            echo "<a href='?id={$p['id']}'>{$p['name']} (ID: {$p['id']}) - {$p['camp_name']}</a>";
        }
        echo '</div>';

        exit;
    }

    // 参加者情報取得
    $participant = $participantModel->find($participantId);
    if (!$participant) {
        echo '<div class="error-box"><strong>エラー:</strong> 参加者ID ' . htmlspecialchars($participantId) . ' が見つかりません</div>';
        exit;
    }

    $camp = $campModel->find($participant['camp_id']);
    if (!$camp) {
        echo '<div class="error-box"><strong>エラー:</strong> 合宿情報が見つかりません</div>';
        exit;
    }

    // 参加者情報表示
    echo '<div class="info-box">';
    echo '<h2>参加者情報</h2>';
    echo '<table>';
    echo '<tr><th>ID</th><td>' . htmlspecialchars($participant['id']) . '</td></tr>';
    echo '<tr><th>名前</th><td>' . htmlspecialchars($participant['name']) . '</td></tr>';
    echo '<tr><th>参加</th><td>' . $participant['join_day'] . '日目 ' . htmlspecialchars($participant['join_timing']) . ' から</td></tr>';
    echo '<tr><th>離脱</th><td>' . $participant['leave_day'] . '日目 ' . htmlspecialchars($participant['leave_timing']) . ' まで</td></tr>';
    echo '</table>';
    echo '</div>';

    // 合宿情報表示
    echo '<div class="info-box">';
    echo '<h2>合宿情報</h2>';
    echo '<table>';
    echo '<tr><th>合宿名</th><td>' . htmlspecialchars($camp['name']) . '</td></tr>';
    echo '<tr><th>泊数</th><td>' . $camp['nights'] . '泊' . ($camp['nights'] + 1) . '日</td></tr>';
    echo '<tr><th>朝食</th><td>追加: ¥' . number_format($camp['breakfast_add_price']) . ' / 欠食: -¥' . number_format($camp['breakfast_remove_price']) . '</td></tr>';
    echo '<tr><th>昼食</th><td>追加: ¥' . number_format($camp['lunch_add_price']) . ' / 欠食: -¥' . number_format($camp['lunch_remove_price']) . '</td></tr>';
    echo '<tr><th>夕食</th><td>追加: ¥' . number_format($camp['dinner_add_price']) . ' / 欠食: -¥' . number_format($camp['dinner_remove_price']) . '</td></tr>';
    echo '</table>';
    echo '</div>';

    // 手動調整を確認
    $mealAdjustments = $participantModel->getMealAdjustments($participantId);

    if (!empty($mealAdjustments)) {
        echo '<div class="warning-box">';
        echo '<h2>⚠️ 手動食事調整（meal_adjustments テーブル）</h2>';
        echo '<p><strong>以下の手動調整が登録されています。これが計算に影響している可能性があります。</strong></p>';
        echo '<table>';
        echo '<tr><th>日</th><th>食事</th><th>調整</th></tr>';
        foreach ($mealAdjustments as $adj) {
            $mealNames = ['breakfast' => '朝食', 'lunch' => '昼食', 'dinner' => '夕食'];
            $mealName = $mealNames[$adj['meal_type']] ?? $adj['meal_type'];
            $adjType = $adj['adjustment_type'] === 'add' ? '追加' : '欠食';
            $adjClass = $adj['adjustment_type'] === 'add' ? 'success-box' : 'error-box';

            echo '<tr>';
            echo '<td>' . $adj['day_number'] . '日目</td>';
            echo '<td>' . $mealName . '</td>';
            echo '<td><span class="' . $adjClass . '" style="display:inline-block; padding:4px 8px;">' . $adjType . '</span></td>';
            echo '</tr>';
        }
        echo '</table>';

        // 手動調整の合計を計算
        $manualTotal = 0;
        foreach ($mealAdjustments as $adj) {
            if ($adj['adjustment_type'] === 'add') {
                $manualTotal += $camp[$adj['meal_type'] . '_add_price'];
            } else {
                $manualTotal -= $camp[$adj['meal_type'] . '_remove_price'];
            }
        }
        echo '<p><strong>手動調整合計: ' . ($manualTotal >= 0 ? '+' : '') . '¥' . number_format($manualTotal) . '</strong></p>';
        echo '</div>';
    } else {
        echo '<div class="success-box">';
        echo '<h2>✅ 手動食事調整</h2>';
        echo '<p>手動調整はありません（正常）</p>';
        echo '</div>';
    }

    // 自動計算を実行
    echo '<h2>🧮 自動食事調整の計算（CalculationService）</h2>';

    $totalNights = $camp['nights'];
    $autoTotal = 0;
    $autoDetails = [];

    for ($night = 1; $night <= $totalNights; $night++) {
        $isStaying = isParticipantStayingNight($participant, $night);

        echo '<div class="night-section">';
        echo '<h3>' . $night . '泊目（' . $night . '日目の夜）</h3>';
        echo '<p><strong>宿泊: ' . ($isStaying ? '<span style="color:#4CAF50;">YES</span>' : '<span style="color:#999;">NO</span>') . '</strong></p>';

        $dinnerDay = $night;
        $breakfastDay = $night + 1;
        $lunchDay = $night + 1;

        // 夕食
        $eatsDinner = doesParticipantEatMeal($participant, $dinnerDay, 'dinner', $camp);
        echo '<div class="meal-item">';
        echo '<span>' . $dinnerDay . '日目夕食: ' . ($eatsDinner ? '<strong>食べる</strong>' : '<span style="color:#999;">食べない</span>') . '</span>';

        if ($isStaying && !$eatsDinner) {
            $removePrice = $camp['dinner_remove_price'];
            $autoTotal -= $removePrice;
            $autoDetails[] = $dinnerDay . '日目夕食欠食 -' . $removePrice . '円';
            echo '<span class="adjustment remove">欠食 -¥' . number_format($removePrice) . '</span>';
        } elseif (!$isStaying && $eatsDinner) {
            $addPrice = $camp['dinner_add_price'];
            $autoTotal += $addPrice;
            $autoDetails[] = $dinnerDay . '日目夕食追加 +' . $addPrice . '円';
            echo '<span class="adjustment">追加 +¥' . number_format($addPrice) . '</span>';
        }
        echo '</div>';

        // 朝食
        $eatsBreakfast = doesParticipantEatMeal($participant, $breakfastDay, 'breakfast', $camp);
        echo '<div class="meal-item">';
        echo '<span>' . $breakfastDay . '日目朝食: ' . ($eatsBreakfast ? '<strong>食べる</strong>' : '<span style="color:#999;">食べない</span>') . '</span>';

        if ($isStaying && !$eatsBreakfast) {
            $removePrice = $camp['breakfast_remove_price'];
            $autoTotal -= $removePrice;
            $autoDetails[] = $breakfastDay . '日目朝食欠食 -' . $removePrice . '円';
            echo '<span class="adjustment remove">欠食 -¥' . number_format($removePrice) . '</span>';
        } elseif (!$isStaying && $eatsBreakfast) {
            $addPrice = $camp['breakfast_add_price'];
            $autoTotal += $addPrice;
            $autoDetails[] = $breakfastDay . '日目朝食追加 +' . $addPrice . '円';
            echo '<span class="adjustment">追加 +¥' . number_format($addPrice) . '</span>';
        }
        echo '</div>';

        // 昼食
        $eatsLunch = doesParticipantEatMeal($participant, $lunchDay, 'lunch', $camp);
        echo '<div class="meal-item">';
        echo '<span>' . $lunchDay . '日目昼食: ' . ($eatsLunch ? '<strong>食べる</strong>' : '<span style="color:#999;">食べない</span>') . '</span>';

        if ($isStaying && !$eatsLunch) {
            $removePrice = $camp['lunch_remove_price'];
            $autoTotal -= $removePrice;
            $autoDetails[] = $lunchDay . '日目昼食欠食 -' . $removePrice . '円';
            echo '<span class="adjustment remove">欠食 -¥' . number_format($removePrice) . '</span>';
        } elseif (!$isStaying && $eatsLunch) {
            $addPrice = $camp['lunch_add_price'];
            $autoTotal += $addPrice;
            $autoDetails[] = $lunchDay . '日目昼食追加 +' . $addPrice . '円';
            echo '<span class="adjustment">追加 +¥' . number_format($addPrice) . '</span>';
        }
        echo '</div>';

        echo '</div>';
    }

    // 結果表示
    echo '<div class="success-box">';
    echo '<h2>📊 計算結果</h2>';
    echo '<table>';
    echo '<tr><th>自動調整（CalculationService）</th><td style="font-size:20px; font-weight:bold; color:#2196F3;">' . ($autoTotal >= 0 ? '+' : '') . '¥' . number_format($autoTotal) . '</td></tr>';

    if (!empty($mealAdjustments)) {
        echo '<tr><th>手動調整（meal_adjustments）</th><td style="font-size:20px; font-weight:bold; color:#ff9800;">' . ($manualTotal >= 0 ? '+' : '') . '¥' . number_format($manualTotal) . '</td></tr>';
        $finalTotal = $autoTotal + $manualTotal;
        echo '<tr><th>合計</th><td style="font-size:24px; font-weight:bold; color:#333;">' . ($finalTotal >= 0 ? '+' : '') . '¥' . number_format($finalTotal) . '</td></tr>';
    }
    echo '</table>';
    echo '</div>';

    if (!empty($mealAdjustments)) {
        echo '<div class="error-box">';
        echo '<h2>⚠️ 問題が見つかりました</h2>';
        echo '<p><strong>手動調整レコードが存在します。</strong>これは旧ロジックが生成したレコードの可能性があります。</p>';
        echo '<p>修正するには、以下のSQLを実行してください:</p>';
        echo '<code style="display:block; padding:15px; background:#f5f5f5; margin:10px 0;">TRUNCATE TABLE meal_adjustments;</code>';
        echo '</div>';
    } else {
        echo '<div class="success-box">';
        echo '<h2>✅ 正常です</h2>';
        echo '<p>手動調整レコードはありません。自動計算のみが適用されています。</p>';
        echo '</div>';
    }

} catch (Exception $e) {
    echo '<div class="error-box">';
    echo '<strong>エラーが発生しました:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
}

// ヘルパー関数
function isParticipantStayingNight(array $participant, int $nightNumber): bool
{
    $joinDay = $participant['join_day'];
    $leaveDay = $participant['leave_day'];

    if ($joinDay > $nightNumber) {
        return false;
    }

    if ($leaveDay > $nightNumber) {
        return true;
    }

    return false;
}

function doesParticipantEatMeal(array $participant, int $day, string $mealType, array $camp): bool
{
    $joinDay = $participant['join_day'];
    $leaveDay = $participant['leave_day'];
    $joinTiming = $participant['join_timing'];
    $leaveTiming = $participant['leave_timing'];

    if ($day < $joinDay || $day > $leaveDay) {
        return false;
    }

    $joinMealOrder = [
        'outbound_bus' => 0,
        'breakfast' => 1,
        'morning' => 2,
        'lunch' => 3,
        'afternoon' => 4,
        'dinner' => 5,
        'night' => 6,
        'lodging' => 7,
    ];

    $leaveMealOrder = [
        'before_breakfast' => 0,
        'breakfast' => 1,
        'morning' => 2,
        'lunch' => 3,
        'afternoon' => 4,
        'dinner' => 5,
        'night' => 6,
        'lodging' => 7,
        'return_bus' => 8,
    ];

    $mealOrderValue = [
        'breakfast' => 1,
        'lunch' => 3,
        'dinner' => 5,
    ];

    $mealValue = $mealOrderValue[$mealType] ?? 0;

    if ($day === $joinDay) {
        $joinValue = $joinMealOrder[$joinTiming] ?? 0;
        if ($mealValue < $joinValue) {
            return false;
        }
    }

    if ($day === $leaveDay) {
        $leaveValue = $leaveMealOrder[$leaveTiming] ?? 8;
        if ($mealValue > $leaveValue) {
            return false;
        }
    }

    return true;
}
?>

    </div>
</body>
</html>
