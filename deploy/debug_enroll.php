<?php
/**
 * 入会フォーム送信のデバッグ
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');

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

echo "<h1>入会フォーム送信デバッグ</h1>";

// テストデータ
$testData = [
    'name_kanji' => 'テスト 太郎',
    'name_kana' => 'テスト タロウ',
    'gender' => 'male',
    'birthdate' => '2006-04-01',
    'student_id' => '1Y25F158-5',
    'faculty' => '先進理工学部',
    'department' => '電気・情報生命工学科',
    'enrollment_year' => '2025',
    'phone' => '090-0000-0000',
    'address' => 'テスト住所',
    'emergency_contact' => '03-0000-0000',
    'email' => 'test@example.com',
    'line_name' => 'test_user',
    'allergy' => '',
    'sns_allowed' => '1',
    'sports_registration_no' => '',
];

echo "<h2>1. StudentIdParserServiceのテスト</h2>";
try {
    $parser = new StudentIdParserService();
    $parsed = $parser->parse($testData['student_id']);

    echo "<pre>";
    print_r($parsed);
    echo "</pre>";

    if ($parsed['is_valid']) {
        echo "<p style='color:green'>✓ 学籍番号の解析成功</p>";

        // 学年計算
        $grade = $parser->calculateGrade((int)$testData['enrollment_year']);
        echo "<p>計算された学年: <strong>{$grade}</strong></p>";

        $testData['grade'] = (string)$grade;
    } else {
        echo "<p style='color:red'>✗ 学籍番号の解析失敗: {$parsed['error']}</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>2. Member::create()のテスト</h2>";
try {
    $member = new Member();

    // ステータス追加
    $testData['status'] = 'pending';

    echo "<h3>登録するデータ:</h3>";
    echo "<pre>";
    print_r($testData);
    echo "</pre>";

    // 実際には登録しない（dry-run）
    echo "<p>※ 実際の登録は行いません（テストモード）</p>";

    // 必須フィールドチェック
    $requiredFields = [
        'name_kanji', 'name_kana', 'gender', 'grade', 'faculty', 'department',
        'student_id', 'phone', 'address', 'emergency_contact', 'birthdate', 'line_name'
    ];

    $missing = [];
    foreach ($requiredFields as $field) {
        if (empty($testData[$field])) {
            $missing[] = $field;
        }
    }

    if (empty($missing)) {
        echo "<p style='color:green'>✓ すべての必須フィールドが揃っています</p>";
    } else {
        echo "<p style='color:red'>✗ 不足しているフィールド: " . implode(', ', $missing) . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>3. セッションのテスト</h2>";
$_SESSION['test_enrollment'] = $testData;
if (isset($_SESSION['test_enrollment'])) {
    echo "<p style='color:green'>✓ セッションに保存できました</p>";
    echo "<p>保存されたデータの項目数: " . count($_SESSION['test_enrollment']) . "</p>";
    unset($_SESSION['test_enrollment']);
} else {
    echo "<p style='color:red'>✗ セッションに保存できませんでした</p>";
}

echo "<hr>";
echo "<p>デバッグ完了</p>";
echo "<p><a href='/enroll'>入会フォームに戻る</a></p>";
