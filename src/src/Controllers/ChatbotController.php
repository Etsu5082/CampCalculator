<?php
/**
 * チャットボットコントローラー
 */
class ChatbotController
{
    private ChatbotService $chatbotService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
    }

    /**
     * チャットボットの状態を取得
     */
    public function status(array $params): void
    {
        Auth::requireAuth();

        Response::json([
            'success' => true,
            'data' => [
                'enabled' => $this->chatbotService->isEnabled()
            ]
        ]);
    }

    /**
     * 質問に回答
     */
    public function ask(array $params): void
    {
        Auth::requireAuth();

        // JSONリクエストを取得
        $input = json_decode(file_get_contents('php://input'), true);
        $question = trim($input['question'] ?? '');
        $history = $input['history'] ?? [];

        if (empty($question)) {
            Response::json([
                'success' => false,
                'error' => '質問を入力してください'
            ], 400);
            return;
        }

        // 質問が長すぎる場合
        if (mb_strlen($question) > 500) {
            Response::json([
                'success' => false,
                'error' => '質問は500文字以内で入力してください'
            ], 400);
            return;
        }

        // 会話履歴を検証・サニタイズ（最大10件）
        $sanitizedHistory = [];
        if (is_array($history)) {
            foreach (array_slice($history, -10) as $item) {
                if (isset($item['role']) && isset($item['content'])
                    && in_array($item['role'], ['user', 'assistant'])) {
                    $sanitizedHistory[] = [
                        'role' => $item['role'],
                        'content' => mb_substr(trim($item['content']), 0, 1000)
                    ];
                }
            }
        }

        $result = $this->chatbotService->ask($question, $sanitizedHistory);

        if ($result['success']) {
            Response::json([
                'success' => true,
                'data' => [
                    'answer' => $result['answer'],
                    'sources' => $result['sources'] ?? []
                ]
            ]);
        } else {
            Response::json([
                'success' => false,
                'error' => $result['error']
            ], 500);
        }
    }
}
