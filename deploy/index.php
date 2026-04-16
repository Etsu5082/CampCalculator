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

// 合宿申し込みURL管理
$router->get('/api/camps/{id}/application-url', 'CampController@getApplicationUrl');
$router->post('/api/camps/{id}/application-url', 'CampController@generateApplicationUrl');
$router->put('/api/camps/{id}/application-url', 'CampController@updateApplicationUrl');
$router->get('/api/camps/{id}/applications', 'CampController@getApplications');

// タイムスロットルート
$router->get('/api/camps/{id}/time-slots', 'TimeSlotController@index');
$router->put('/api/camps/{id}/time-slots', 'TimeSlotController@update');

// 参加者ルート
$router->get('/api/camps/{id}/participants', 'ParticipantController@index');
$router->post('/api/camps/{id}/participants', 'ParticipantController@store');
$router->post('/api/camps/{id}/participants/import', 'ParticipantController@importCsv');
$router->post('/api/camps/{id}/participants/check-duplicate', 'ParticipantController@checkDuplicate');
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
$router->get('/api/camps/{id}/export/xlsx', 'ExportController@xlsx');
$router->get('/api/camps/{id}/export/insurance-roster', 'ExportController@insuranceRoster');
$router->get('/api/camps/{id}/export/participant-roster-cosmo', 'ExportController@participantRosterCosmo');
$router->get('/api/camps/{id}/export/headcount-report', 'ExportController@headcountReport');
$router->get('/api/camps/{id}/export/headcount-report-mycom', 'ExportController@headcountReportMycom');

// チャットボットルート
$router->get('/api/chatbot/status', 'ChatbotController@status');
$router->post('/api/chatbot/ask', 'ChatbotController@ask');

// PDF読み取りルート
$router->get('/pdf/upload', 'PdfUploadController@index');
$router->post('/pdf/upload', 'PdfUploadController@upload');
$router->get('/pdf/review', 'PdfUploadController@review');
$router->post('/pdf/apply', 'PdfUploadController@apply');
$router->get('/pdf/cancel', 'PdfUploadController@cancel');

// 会員管理ルート（新規）
// 注意: 固定パスを先に登録（{id}パラメータより前）
$router->get('/api/members', 'MemberController@index');
$router->get('/api/members/pending', 'MemberController@pending');
$router->get('/api/members/pending/count', 'MemberController@pendingCount');
$router->get('/api/members/parse-student-id', 'MemberController@parseStudentId');
$router->post('/api/members', 'MemberController@store');
$router->post('/api/members/import', 'MemberController@import');
// パラメータ付きルートは後ろに配置
$router->get('/api/members/{id}', 'MemberController@show');
$router->put('/api/members/{id}', 'MemberController@update');
$router->delete('/api/members/{id}', 'MemberController@destroy');
$router->post('/api/members/{id}/approve', 'MemberController@approve');
$router->post('/api/members/{id}/reject', 'MemberController@reject');

// 年度管理ルート
$router->get('/api/academic-years', 'AcademicYearController@index');
$router->get('/api/academic-years/current', 'AcademicYearController@getCurrent');
$router->post('/api/academic-years', 'AcademicYearController@store');
$router->post('/api/academic-years/create-next', 'AcademicYearController@createNext');
$router->post('/api/academic-years/set-current', 'AcademicYearController@setCurrent');
$router->post('/api/academic-years/set-enrollment-open', 'AcademicYearController@setEnrollmentOpen');

// ページルート（HTML表示）
$router->get('/', 'PageController@index');
$router->get('/login', 'PageController@login');
$router->get('/camps', 'PageController@camps');
$router->get('/camps/{id}', 'PageController@campDetail');
$router->get('/camps/{id}/result', 'PageController@result');
$router->get('/camps/{id}/partial-schedule', 'PageController@partialSchedule');
$router->get('/guide', 'PageController@guide');
$router->get('/members', 'MemberController@indexPage');
$router->get('/members/pending', 'MemberController@pendingPage');
$router->get('/academic-years', 'AcademicYearController@indexPage');

// 会員ポータルルート（公開ページ）
$router->get('/portal', 'PortalController@index');

// 入会フォームルート（公開ページ）
$router->get('/enroll', 'EnrollmentController@form');
$router->post('/enroll', 'EnrollmentController@submit');
$router->get('/enroll/confirm', 'EnrollmentController@confirm');
$router->get('/enroll/complete', 'EnrollmentController@complete');

// 継続入会フォームルート（公開ページ）
$router->get('/renew', 'RenewalController@search');
$router->get('/api/renew/search-members', 'RenewalController@searchMembers');
$router->get('/renew/confirm', 'RenewalController@confirm');
$router->get('/renew/review', 'RenewalController@review');
$router->post('/api/renew/submit', 'RenewalController@submit');
$router->get('/renew/complete', 'RenewalController@complete');

// 合宿申し込みルート（公開・認証不要）
$router->get('/apply/{token}', 'ApplicationController@form');
$router->get('/apply/{token}/search', 'ApplicationController@searchMembers');
$router->get('/apply/{token}/confirm', 'ApplicationController@confirmInfo');
$router->get('/apply/{token}/schedule', 'ApplicationController@schedule');
$router->get('/apply/{token}/review', 'ApplicationController@review');
$router->post('/apply/{token}/submit', 'ApplicationController@submit');
$router->get('/apply/{token}/complete', 'ApplicationController@complete');

// ルーティング実行
$router->dispatch();
