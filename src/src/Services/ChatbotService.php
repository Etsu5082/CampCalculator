<?php
/**
 * チャットボットサービス（Anthropic API連携）
 */
class ChatbotService
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private array $knowledgeBase;
    private bool $enabled;

    public function __construct()
    {
        $config = require CONFIG_PATH . '/ai.php';
        $this->apiKey = $config['anthropic_api_key'];
        $this->model = $config['model'];
        $this->maxTokens = $config['max_tokens'];
        $this->enabled = $config['enabled'] && !empty($this->apiKey);

        // ナレッジベースを読み込み
        $knowledgePath = BASE_PATH . '/data/knowledge_base.json';
        if (file_exists($knowledgePath)) {
            $this->knowledgeBase = json_decode(file_get_contents($knowledgePath), true);
        } else {
            $this->knowledgeBase = [];
        }
    }

    /**
     * 機能が有効かどうか
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * 質問に回答する
     */
    public function ask(string $question): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'AI機能が無効です。APIキーを設定してください。'
            ];
        }

        try {
            // 関連するナレッジを検索
            $relevantKnowledge = $this->searchKnowledge($question);

            // プロンプトを構築
            $systemPrompt = $this->buildSystemPrompt($relevantKnowledge);

            // Anthropic APIを呼び出し
            $response = $this->callAnthropicApi($systemPrompt, $question);

            return [
                'success' => true,
                'answer' => $response,
                'sources' => array_map(fn($k) => $k['title'], $relevantKnowledge)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'エラーが発生しました: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 質問に関連するナレッジを検索
     */
    private function searchKnowledge(string $question): array
    {
        if (empty($this->knowledgeBase['sections'])) {
            return [];
        }

        $results = [];
        $questionLower = mb_strtolower($question);

        foreach ($this->knowledgeBase['sections'] as $section) {
            $score = 0;

            // キーワードマッチング
            foreach ($section['keywords'] as $keyword) {
                if (mb_strpos($questionLower, mb_strtolower($keyword)) !== false) {
                    $score += 10;
                }
            }

            // タイトルマッチング
            if (mb_strpos($questionLower, mb_strtolower($section['title'])) !== false) {
                $score += 5;
            }

            // コンテンツ内の単語マッチング
            $words = preg_split('/\s+/', $question);
            foreach ($words as $word) {
                if (mb_strlen($word) >= 2 && mb_strpos($section['content'], $word) !== false) {
                    $score += 2;
                }
            }

            if ($score > 0) {
                $section['score'] = $score;
                $results[] = $section;
            }
        }

        // スコアでソートして上位3件を返す
        usort($results, fn($a, $b) => $b['score'] - $a['score']);
        return array_slice($results, 0, 3);
    }

    /**
     * システムプロンプトを構築
     */
    private function buildSystemPrompt(array $relevantKnowledge): string
    {
        $prompt = "あなたは「合宿費用計算アプリ」のサポートアシスタントです。\n";
        $prompt .= "ユーザーからの質問に、以下のナレッジベースの情報を基に回答してください。\n";
        $prompt .= "回答は簡潔で分かりやすく、日本語で行ってください。\n";
        $prompt .= "ナレッジベースにない情報については「その質問についてはお答えできません。管理者にお問い合わせください。」と回答してください。\n\n";

        $prompt .= "【ナレッジベース】\n";

        if (empty($relevantKnowledge)) {
            // 関連ナレッジがない場合は全セクションのタイトルを提供
            $prompt .= "該当する詳細情報が見つかりませんでした。\n";
            $prompt .= "このアプリでは以下の機能があります：\n";
            foreach ($this->knowledgeBase['sections'] ?? [] as $section) {
                $prompt .= "- " . $section['title'] . "\n";
            }
        } else {
            foreach ($relevantKnowledge as $knowledge) {
                $prompt .= "---\n";
                $prompt .= "【" . $knowledge['title'] . "】\n";
                $prompt .= $knowledge['content'] . "\n";
            }
        }

        // 連絡先情報を追加
        if (!empty($this->knowledgeBase['contact'])) {
            $contact = $this->knowledgeBase['contact'];
            $prompt .= "\n---\n";
            $prompt .= "【お問い合わせ先】\n";
            $prompt .= $contact['name'] . "\n";
            $prompt .= "メール: " . implode(', ', $contact['email']) . "\n";
            $prompt .= "電話: " . $contact['phone'] . "\n";
        }

        return $prompt;
    }

    /**
     * Anthropic APIを呼び出し
     */
    private function callAnthropicApi(string $systemPrompt, string $userMessage): string
    {
        $url = 'https://api.anthropic.com/v1/messages';

        $data = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'system' => $systemPrompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('API接続エラー: ' . $error);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            throw new Exception('API エラー (' . $httpCode . '): ' . $errorMessage);
        }

        $result = json_decode($response, true);

        if (!isset($result['content'][0]['text'])) {
            throw new Exception('APIレスポンスの形式が不正です');
        }

        return $result['content'][0]['text'];
    }
}
