<?php
/**
 * アプリケーション設定
 */

// URL生成ヘルパー関数（nginx対応）
if (!function_exists('url')) {
    function url(string $path): string {
        return '/index.php?route=' . ltrim($path, '/');
    }
}

// API URL生成
if (!function_exists('apiUrl')) {
    function apiUrl(string $path): string {
        return '/index.php?route=' . ltrim($path, '/');
    }
}

return [
    'name' => '合宿費用計算アプリ',
    'version' => '1.0.0',
    'debug' => true, // 本番環境ではfalseに

    // セッション設定
    'session' => [
        'lifetime' => 86400, // 24時間
        'name' => 'camp_calc_session',
    ],

    // デフォルト値
    'defaults' => [
        'nights' => 3,
        'lodging_fee_per_night' => 8000,
        'breakfast_add_price' => 600,
        'breakfast_remove_price' => 400,
        'lunch_add_price' => 990,
        'lunch_remove_price' => 440,
        'dinner_add_price' => 1200,
        'dinner_remove_price' => 800,
        'insurance_fee' => 500,
    ],
];
