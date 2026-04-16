<?php
/**
 * インポートテスト用スクリプト
 */

// エラー表示
ini_set('display_errors', 1);
error_reporting(E_ALL);

// セッション開始
session_start();

// 定数定義
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');

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

// 設定ファイル読み込み
require_once CONFIG_PATH . '/database.php';

// データベース接続
$db = Database::getInstance();

echo "<h1>会員インポートテスト</h1>";

// 1. membersテーブルが存在するか確認
echo "<h2>1. membersテーブルの存在確認</h2>";
try {
    $result = $db->fetch("SHOW TABLES LIKE 'members'");
    if ($result) {
        echo "<p style='color:green'>✓ membersテーブルが存在します</p>";
    } else {
        echo "<p style='color:red'>✗ membersテーブルが存在しません！DBマイグレーションを実行してください。</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
    exit;
}

// 2. 現在の会員数を確認
echo "<h2>2. 現在の会員数</h2>";
try {
    $result = $db->fetch("SELECT COUNT(*) as total FROM members");
    $total = $result['total'] ?? 0;
    echo "<p>登録会員数: <strong>{$total}名</strong></p>";
} catch (Exception $e) {
    echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
}

// 3. ステータス別の集計
echo "<h2>3. ステータス別の集計</h2>";
try {
    $results = $db->fetchAll("SELECT status, COUNT(*) as count FROM members GROUP BY status");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ステータス</th><th>件数</th></tr>";
    foreach ($results as $row) {
        echo "<tr><td>{$row['status']}</td><td>{$row['count']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
}

// 4. 最初の10件を表示
echo "<h2>4. 最初の10件のデータ</h2>";
try {
    $results = $db->fetchAll("SELECT id, name_kanji, name_kana, student_id, status FROM members LIMIT 10");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>名前</th><th>カナ</th><th>学籍番号</th><th>ステータス</th></tr>";
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name_kanji']}</td>";
        echo "<td>{$row['name_kana']}</td>";
        echo "<td>{$row['student_id']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
}

// 5. Member::search() のテスト
echo "<h2>5. Member::search() のテスト</h2>";
try {
    $member = new Member();

    // フィルタなしで検索
    $results = $member->search([], 20, 0);
    echo "<p>フィルタなし: <strong>" . count($results) . "件</strong></p>";

    // status='active'で検索
    $results = $member->search(['status' => 'active'], 20, 0);
    echo "<p>status='active': <strong>" . count($results) . "件</strong></p>";

    // 件数をカウント
    $count = $member->countSearch(['status' => 'active']);
    echo "<p>countSearch(status='active'): <strong>{$count}件</strong></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p>テスト完了</p>";
