<?php
/**
 * 認証コントローラー
 */
class AuthController
{
    /**
     * ログイン
     */
    public function login(array $params): void
    {
        $password = Request::get('password');

        if (empty($password)) {
            Response::error('パスワードを入力してください', 400, 'VALIDATION_ERROR');
        }

        if (Auth::login($password)) {
            Response::success([], 'ログインしました');
        } else {
            Response::error('パスワードが正しくありません', 401, 'INVALID_PASSWORD');
        }
    }

    /**
     * ログアウト
     */
    public function logout(array $params): void
    {
        Auth::logout();
        Response::success([], 'ログアウトしました');
    }

    /**
     * 認証状態チェック
     */
    public function check(array $params): void
    {
        if (Auth::check()) {
            Response::success(['authenticated' => true]);
        } else {
            Response::success(['authenticated' => false]);
        }
    }
}
