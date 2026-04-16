<?php
/**
 * メール送信設定
 */

return [
    // SMTP設定（お名前.comのメールサーバー）
    'smtp_host' => 'smtp.onamae.com',
    'smtp_port' => 587,
    'smtp_user' => 'noreply@example.com',  // 実際のメールアドレスに変更
    'smtp_pass' => '',  // 実際のパスワードに変更

    // 送信元情報
    'from_address' => 'noreply@example.com',  // 実際のメールアドレスに変更
    'from_name' => 'レッセフェールT.C. 会計システム',

    // 管理者メールアドレス
    'admin_email' => 'kohetsu.watanabe@gmail.com',

    // メール送信の有効/無効
    'enabled' => true,
];
