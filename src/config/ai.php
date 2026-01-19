<?php
/**
 * AI (Anthropic API) 設定
 */

return [
    // Anthropic APIキー（環境変数から取得、または直接設定）
    'anthropic_api_key' => getenv('ANTHROPIC_API_KEY') ?: '',

    // 使用するモデル（コスト効率の良いHaikuを推奨）
    'model' => 'claude-3-haiku-20240307',

    // 最大トークン数
    'max_tokens' => 1024,

    // 機能の有効/無効
    'enabled' => true,
];
