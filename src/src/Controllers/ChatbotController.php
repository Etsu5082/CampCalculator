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

        $result = $this->chatbotService->ask($question);

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
