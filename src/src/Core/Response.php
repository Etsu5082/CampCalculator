<?php
/**
 * レスポンスヘルパークラス
 */
class Response
{
    /**
     * JSON レスポンス
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 成功レスポンス
     */
    public static function success(array $data = [], string $message = ''): void
    {
        $response = ['success' => true];
        if (!empty($data)) {
            $response['data'] = $data;
        }
        if (!empty($message)) {
            $response['message'] = $message;
        }
        self::json($response);
    }

    /**
     * エラーレスポンス
     */
    public static function error(string $message, int $status = 400, string $code = 'ERROR'): void
    {
        self::json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }

    /**
     * バリデーションエラーレスポンス
     */
    public static function validationError(array $errors): void
    {
        self::json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => '入力内容に誤りがあります',
                'details' => $errors,
            ],
        ], 422);
    }

    /**
     * 認証エラーレスポンス
     */
    public static function unauthorized(string $message = '認証が必要です'): void
    {
        self::error($message, 401, 'UNAUTHORIZED');
    }

    /**
     * リダイレクト
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
