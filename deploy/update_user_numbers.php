<?php
/**
 * 都営登録番号（user_number）自動更新スクリプト
 * users1.csvから学籍番号に基づいてuser_numberを会員名簿に反映
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
        SRC_PATH . '/Models/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 設定ファイル読み込み
require_once CONFIG_PATH . '/database.php';

// データベース接続
$db = Database::getInstance();

echo "<h1>都営登録番号 自動更新</h1>";

// CSVファイルパス
$csvFile = __DIR__ . '/users1.csv';

if (!file_exists($csvFile)) {
    echo "<p style='color:red'>エラー: users1.csv が見つかりません</p>";
    exit;
}

echo "<h2>処理開始</h2>";

$updated = 0;
$notFound = 0;
$errors = [];

// CSVファイルを読み込み
$handle = fopen($csvFile, 'r');

if (!$handle) {
    echo "<p style='color:red'>エラー: CSVファイルを開けませんでした</p>";
    exit;
}

// ヘッダー行をスキップ
$headers = fgetcsv($handle);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>学籍番号</th><th>名前</th><th>user_number</th><th>結果</th></tr>";

// データ行を処理
while (($row = fgetcsv($handle)) !== false) {
    $name = $row[0];
    $kana = $row[1];
    $year = $row[2];
    $userNumber = $row[3];
    $studentId = $row[4]; // password列が学籍番号

    echo "<tr>";
    echo "<td>" . htmlspecialchars($studentId) . "</td>";
    echo "<td>" . htmlspecialchars($name) . "</td>";
    echo "<td>" . htmlspecialchars($userNumber) . "</td>";

    try {
        // 学籍番号で会員を検索
        $member = $db->fetch(
            "SELECT id, name_kanji, sports_registration_no FROM members WHERE student_id = ?",
            [$studentId]
        );

        if ($member) {
            // 既にuser_numberが登録されている場合はスキップ
            if ($member['sports_registration_no'] && $member['sports_registration_no'] === $userNumber) {
                echo "<td style='color:gray'>登録済み</td>";
            } else {
                // user_numberを更新
                $db->execute(
                    "UPDATE members SET sports_registration_no = ? WHERE id = ?",
                    [$userNumber, $member['id']]
                );

                echo "<td style='color:green'>✓ 更新完了</td>";
                $updated++;
            }
        } else {
            echo "<td style='color:orange'>会員が見つかりません</td>";
            $notFound++;
        }
    } catch (Exception $e) {
        echo "<td style='color:red'>エラー: " . htmlspecialchars($e->getMessage()) . "</td>";
        $errors[] = $studentId . ": " . $e->getMessage();
    }

    echo "</tr>";
}

fclose($handle);

echo "</table>";

echo "<hr>";
echo "<h2>処理結果</h2>";
echo "<ul>";
echo "<li><strong>更新成功:</strong> {$updated}件</li>";
echo "<li><strong>会員が見つからない:</strong> {$notFound}件</li>";
echo "<li><strong>エラー:</strong> " . count($errors) . "件</li>";
echo "</ul>";

if (!empty($errors)) {
    echo "<h3>エラー詳細</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}

echo "<p><a href='/members'>会員一覧に戻る</a></p>";
