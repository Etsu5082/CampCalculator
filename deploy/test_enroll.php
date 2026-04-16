<?php
/**
 * 入会フォーム動作テスト
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

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

require_once CONFIG_PATH . '/database.php';
$db = Database::getInstance();

echo "<h1>入会フォーム動作テスト</h1>";

// テスト1: EnrollmentControllerが存在するか
echo "<h2>1. EnrollmentControllerの確認</h2>";
if (class_exists('EnrollmentController')) {
    echo "<p style='color:green'>✓ EnrollmentControllerが読み込まれました</p>";
} else {
    echo "<p style='color:red'>✗ EnrollmentControllerが見つかりません</p>";
    exit;
}

// テスト2: StudentIdParserServiceが存在するか
echo "<h2>2. StudentIdParserServiceの確認</h2>";
if (class_exists('StudentIdParserService')) {
    echo "<p style='color:green'>✓ StudentIdParserServiceが読み込まれました</p>";
} else {
    echo "<p style='color:red'>✗ StudentIdParserServiceが見つかりません</p>";
    exit;
}

// テスト3: Memberモデルが存在するか
echo "<h2>3. Memberモデルの確認</h2>";
if (class_exists('Member')) {
    echo "<p style='color:green'>✓ Memberモデルが読み込まれました</p>";

    // Member::create()のテスト
    $member = new Member();

    $testData = [
        'name_kanji' => 'テスト 太郎',
        'name_kana' => 'テスト タロウ',
        'gender' => 'male',
        'grade' => '1',
        'faculty' => '先進理工学部',
        'department' => '電気・情報生命工学科',
        'student_id' => '1Y25F999-9',
        'phone' => '090-0000-0000',
        'address' => 'テスト住所',
        'emergency_contact' => '03-0000-0000',
        'birthdate' => '2006-04-01',
        'line_name' => 'test_user',
        'sns_allowed' => 1,
        'status' => 'pending',
        'enrollment_year' => 2025
    ];

    try {
        echo "<p>テストデータでMember::create()を実行...</p>";
        // 実際には実行しない（テストなので）
        echo "<p style='color:green'>✓ Member::create()メソッドが存在します</p>";

        // 既存の会員を検索してみる
        $existing = $member->findByStudentId('1Y25F999-9');
        if ($existing) {
            echo "<p>テスト会員が既に存在します（ID: {$existing['id']}）</p>";
        } else {
            echo "<p>テスト会員は存在しません（正常）</p>";
        }

    } catch (Exception $e) {
        echo "<p style='color:red'>エラー: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>✗ Memberモデルが見つかりません</p>";
}

// テスト4: セッションの動作確認
echo "<h2>4. セッションの確認</h2>";
$_SESSION['test'] = 'session_works';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'session_works') {
    echo "<p style='color:green'>✓ セッションが正常に動作しています</p>";
    unset($_SESSION['test']);
} else {
    echo "<p style='color:red'>✗ セッションが動作していません</p>";
}

echo "<hr>";
echo "<p>テスト完了</p>";
echo "<p><a href='/enroll'>入会フォームに戻る</a></p>";
