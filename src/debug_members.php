<?php
/**
 * デバッグ用: members テーブルの状態確認
 */

// 定数定義
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('SRC_PATH', BASE_PATH . '/src');

require_once SRC_PATH . '/Core/Database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Members テーブル デバッグ</h1>";

try {
    $db = Database::getInstance();

    // 1. テーブル構造を確認
    echo "<h2>1. テーブル構造</h2>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM members");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 2. 総レコード数
    echo "<h2>2. 総レコード数</h2>";
    $count = $db->fetch("SELECT COUNT(*) as total FROM members");
    echo "<p>総会員数: <strong>" . $count['total'] . "</strong></p>";

    // 3. academic_year ごとの件数
    echo "<h2>3. academic_year ごとの件数</h2>";
    $byYear = $db->fetchAll("SELECT academic_year, COUNT(*) as cnt FROM members GROUP BY academic_year ORDER BY academic_year DESC");
    if (empty($byYear)) {
        echo "<p>データなし</p>";
    } else {
        echo "<table border='1'><tr><th>academic_year</th><th>件数</th></tr>";
        foreach ($byYear as $row) {
            echo "<tr><td>" . htmlspecialchars($row['academic_year']) . "</td><td>" . $row['cnt'] . "</td></tr>";
        }
        echo "</table>";
    }

    // 4. サンプルデータ（最初の10件）
    echo "<h2>4. サンプルデータ（最初の10件）</h2>";
    $samples = $db->fetchAll("SELECT id, name_kanji, name_kana, grade, academic_year, status FROM members LIMIT 10");
    if (empty($samples)) {
        echo "<p>データなし</p>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>名前（漢字）</th><th>名前（かな）</th><th>学年</th><th>academic_year</th><th>status</th></tr>";
        foreach ($samples as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['name_kanji'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['name_kana'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['grade'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['academic_year'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['status'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 5. 検索テスト（2024年度）
    echo "<h2>5. 2024年度の会員（継続入会の検索対象）</h2>";
    $year2024 = $db->fetchAll("SELECT id, name_kanji, academic_year FROM members WHERE academic_year = 2024 LIMIT 10");
    if (empty($year2024)) {
        echo "<p style='color:red;'><strong>2024年度の会員が0件です。これが「該当する会員が見つかりませんでした」の原因です。</strong></p>";
        echo "<p>継続入会機能は前年度（2024年度）の会員を検索します。現在のデータはすべて2025年度になっている可能性があります。</p>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>名前</th><th>academic_year</th></tr>";
        foreach ($year2024 as $row) {
            echo "<tr><td>" . $row['id'] . "</td><td>" . htmlspecialchars($row['name_kanji']) . "</td><td>" . $row['academic_year'] . "</td></tr>";
        }
        echo "</table>";
    }

    // 6. academic_years テーブル
    echo "<h2>6. academic_years テーブル</h2>";
    $academicYears = $db->fetchAll("SELECT * FROM academic_years ORDER BY year DESC");
    echo "<table border='1'><tr><th>ID</th><th>year</th><th>is_current</th><th>enrollment_open</th></tr>";
    foreach ($academicYears as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['year'] . "</td>";
        echo "<td>" . $row['is_current'] . "</td>";
        echo "<td>" . $row['enrollment_open'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<p style='color:red;'>エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
}
