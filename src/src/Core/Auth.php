<?php
/**
 * 認証ヘルパークラス
 */
class Auth
{
    private const SESSION_KEY = 'authenticated';

    /**
     * ログイン
     */
    public static function login(string $password): bool
    {
        $db = Database::getInstance();
        $setting = $db->fetch(
            "SELECT setting_value FROM settings WHERE setting_key = 'password'"
        );

        if (!$setting) {
            return false;
        }

        if (password_verify($password, $setting['setting_value'])) {
            $_SESSION[self::SESSION_KEY] = true;
            $_SESSION['login_time'] = time();
            return true;
        }

        return false;
    }

    /**
     * ログアウト
     */
    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        unset($_SESSION['login_time']);
    }

    /**
     * 認証チェック
     */
    public static function check(): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        // セッション有効期限チェック（24時間）
        $config = require CONFIG_PATH . '/app.php';
        $lifetime = $config['session']['lifetime'] ?? 86400;

        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > $lifetime) {
                self::logout();
                return false;
            }
        }

        return true;
    }

    /**
     * 認証必須ガード
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
                Response::unauthorized();
            } else {
                Response::redirect('/login');
            }
        }
    }

    /**
     * パスワード設定（初期設定用）
     */
    public static function setPassword(string $password): void
    {
        $db = Database::getInstance();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $existing = $db->fetch(
            "SELECT id FROM settings WHERE setting_key = 'password'"
        );

        if ($existing) {
            $db->execute(
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'password'",
                [$hash]
            );
        } else {
            $db->execute(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('password', ?)",
                [$hash]
            );
        }
    }
}
