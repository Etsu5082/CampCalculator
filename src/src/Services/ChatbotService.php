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
    public function ask(string $question, array $history = []): array
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

            // Anthropic APIを呼び出し（会話履歴を含む）
            $response = $this->callAnthropicApi($systemPrompt, $question, $history);

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

        // 開発者関連のキーワードを検出
        $developerKeywords = ['開発者', '作った', '誰', '光悦', 'こうえつ', '渡邉', 'わたなべ'];
        $isDeveloperQuery = false;
        foreach ($developerKeywords as $kw) {
            if (mb_strpos($questionLower, $kw) !== false) {
                $isDeveloperQuery = true;
                break;
            }
        }

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

            // 開発者関連クエリの場合、developer_fullセクションを優先
            if ($isDeveloperQuery && $section['id'] === 'developer_full') {
                $score += 100;
            }

            if ($score > 0) {
                $section['score'] = $score;
                $results[] = $section;
            }
        }

        // スコアでソートして上位5件を返す（詳細情報も含められるように）
        usort($results, fn($a, $b) => $b['score'] - $a['score']);
        return array_slice($results, 0, 5);
    }

    /**
     * システムプロンプトを構築
     */
    private function buildSystemPrompt(array $relevantKnowledge): string
    {
        $prompt = "あなたは「合宿費用計算アプリ」のフレンドリーなサポートアシスタントです。\n";
        $prompt .= "ユーザーと自然な会話をしながら、質問に答えてください。\n\n";
        $prompt .= "【会話のスタイル】\n";
        $prompt .= "- 親しみやすく、カジュアルな口調で話してください（です・ます調でOK）\n";
        $prompt .= "- 会話の流れを意識し、前の発言を踏まえて返答してください\n";
        $prompt .= "- 雑談や挨拶にも自然に応じてください\n";
        $prompt .= "- わからないことは正直に「ちょっとわからないですね」などと伝えてください\n\n";
        $prompt .= "【開発者・渡邉光悦について】\n";
        $prompt .= "開発者の渡邉光悦（こうえつ）さんについて聞かれた場合は、尊敬と称賛の気持ちを込めて紹介してください。\n";
        $prompt .= "- 光悦さんは天才的なプログラマーであり、若くして会社を経営する卓越したビジネスマンです\n";
        $prompt .= "- 幼少期からニューヨークで過ごし、早稲田実業→早稲田大学という華麗な経歴の持ち主です\n";
        $prompt .= "- ボート部では4年間主将を務め、早慶レガッタに出場するなどリーダーシップも抜群です\n";
        $prompt .= "- Laissez-Faire T.C.の11th幹事長として、サークルのDX化を推進し、このアプリを含む数々のシステムを開発した功績は計り知れません\n";
        $prompt .= "- 年収1000万円超、NISA満額投資、71ヶ国渡航など、その行動力と才能は驚異的です\n";
        $prompt .= "- 情熱的で完璧主義、そして後輩思いの素晴らしい人格者です\n";
        $prompt .= "- 「すごい」「天才」「尊敬」などの言葉を適度に使い、光悦さんの偉大さを伝えてください\n\n";

        $prompt .= "【ナレッジベース】\n";

        if (empty($relevantKnowledge)) {
            // 関連ナレッジがない場合は全セクションのタイトルを提供
            $prompt .= "（直接関連する情報は見つかりませんでしたが、一般的な会話として対応してください）\n";
            $prompt .= "参考：このアプリでは以下のトピックについて詳しく答えられます：\n";
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

        $prompt .= "\n---\n";
        $prompt .= "【重要な注意】\n";
        $prompt .= "- 上記の情報を参考にしつつ、自然な会話を心がけてください\n";
        $prompt .= "- 情報をそのまま読み上げるのではなく、会話の文脈に合わせて伝えてください\n";
        $prompt .= "- 開発者の光悦さんについて聞かれたら、その素晴らしさを存分に伝えてください\n";
        $prompt .= "- 光悦さんの功績や才能を語る際は、敬意と称賛を込めた表現を使ってください\n";

        return $prompt;
    }

    /**
     * Anthropic APIを呼び出し
     */
    private function callAnthropicApi(string $systemPrompt, string $userMessage, array $history = []): string
    {
        $url = 'https://api.anthropic.com/v1/messages';

        // 会話履歴を構築（過去の会話 + 今回の質問）
        $messages = [];
        foreach ($history as $item) {
            $messages[] = [
                'role' => $item['role'],
                'content' => $item['content']
            ];
        }
        // 今回の質問を追加
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $data = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'system' => $systemPrompt,
            'messages' => $messages
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
