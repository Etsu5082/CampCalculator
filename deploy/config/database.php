<?php
/**
 * データベース設定
 * 本番環境では適切な値に変更してください
 */

return [
    'host' => 'localhost',
    'dbname' => 'laissezfairetc_campcalc',
    'username' => 'laissezfairetc_campcalc',
    'password' => 'LnsqwqtUySNMbjSL43GD',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
