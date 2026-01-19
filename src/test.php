<?php
// エラー表示を有効化
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP動作テスト</h1>";
echo "<p>PHPバージョン: " . phpversion() . "</p>";

// データベース接続テスト
echo "<h2>データベース接続テスト</h2>";
try {
    $pdo = new PDO(
        'mysql:host=mysql1016.onamae.ne.jp;dbname=c8npo_camp_calc;charset=utf8mb4',
        'c8npo_kohetsu',
        'Mihama02#kw#!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color:green'>✓ データベース接続成功</p>";

    // テーブル確認
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>テーブル一覧: " . implode(', ', $tables) . "</p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>✗ データベース接続エラー: " . $e->getMessage() . "</p>";
}

// ファイル存在確認
echo "<h2>ファイル確認</h2>";
$files = [
    'index.php',
    'config/database.php',
    'config/app.php',
    'src/Core/Router.php',
    'src/Core/Database.php',
    'views/layouts/main.php',
];

foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? "<span style='color:green'>✓</span>" : "<span style='color:red'>✗</span>";
    echo "<p>$status $file</p>";
}

echo "<h2>ディレクトリ構造</h2>";
echo "<pre>";
function listDir($dir, $indent = 0) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        echo str_repeat('  ', $indent) . $item . "\n";
        if (is_dir($dir . '/' . $item) && $indent < 2) {
            listDir($dir . '/' . $item, $indent + 1);
        }
    }
}
listDir(__DIR__);
echo "</pre>";
