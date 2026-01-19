<?php
/**
 * 合宿費用計算アプリ - エントリーポイント
 */

// エラー表示設定（本番環境ではOFFに）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// セッション開始
session_start();

// 文字コード設定
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=UTF-8');

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
require_once CONFIG_PATH . '/app.php';

// データベース接続
$db = Database::getInstance();

// ルーター初期化・実行
$router = new Router();

// 認証ルート
$router->post('/api/auth/login', 'AuthController@login');
$router->post('/api/auth/logout', 'AuthController@logout');
$router->get('/api/auth/check', 'AuthController@check');

// 合宿ルート
$router->get('/api/camps', 'CampController@index');
$router->post('/api/camps', 'CampController@store');
$router->get('/api/camps/{id}', 'CampController@show');
$router->put('/api/camps/{id}', 'CampController@update');
$router->delete('/api/camps/{id}', 'CampController@destroy');
$router->post('/api/camps/{id}/duplicate', 'CampController@duplicate');

// タイムスロットルート
$router->get('/api/camps/{id}/time-slots', 'TimeSlotController@index');
$router->put('/api/camps/{id}/time-slots', 'TimeSlotController@update');

// 参加者ルート
$router->get('/api/camps/{id}/participants', 'ParticipantController@index');
$router->post('/api/camps/{id}/participants', 'ParticipantController@store');
$router->post('/api/camps/{id}/participants/import', 'ParticipantController@importCsv');
$router->delete('/api/camps/{id}/participants/deleteAll', 'ParticipantController@deleteAll');
$router->put('/api/participants/{id}', 'ParticipantController@update');
$router->delete('/api/participants/{id}', 'ParticipantController@destroy');

// 雑費ルート
$router->get('/api/camps/{id}/expenses', 'ExpenseController@index');
$router->post('/api/camps/{id}/expenses', 'ExpenseController@store');
$router->put('/api/expenses/{id}', 'ExpenseController@update');
$router->delete('/api/expenses/{id}', 'ExpenseController@destroy');

// 計算・出力ルート
$router->get('/api/camps/{id}/calculate', 'CalculationController@calculate');
$router->get('/api/camps/{id}/partial-schedule', 'CalculationController@partialSchedule');
$router->get('/api/camps/{id}/export/pdf', 'ExportController@pdf');
$router->get('/api/camps/{id}/export/excel', 'ExportController@excel');

// ページルート（HTML表示）
$router->get('/', 'PageController@index');
$router->get('/login', 'PageController@login');
$router->get('/camps', 'PageController@camps');
$router->get('/camps/{id}', 'PageController@campDetail');
$router->get('/camps/{id}/result', 'PageController@result');
$router->get('/camps/{id}/partial-schedule', 'PageController@partialSchedule');
$router->get('/guide', 'PageController@guide');

// ルーティング実行
$router->dispatch();
