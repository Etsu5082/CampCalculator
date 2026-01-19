<?php
/**
 * リクエストヘルパークラス
 */
class Request
{
    private static ?array $jsonData = null;

    /**
     * JSONリクエストボディを取得
     */
    public static function json(): array
    {
        if (self::$jsonData === null) {
            $input = file_get_contents('php://input');
            self::$jsonData = json_decode($input, true) ?? [];
        }
        return self::$jsonData;
    }

    /**
     * リクエストパラメータ取得（GET, POST, JSON統合）
     */
    public static function get(string $key, $default = null)
    {
        // JSONリクエスト
        $json = self::json();
        if (isset($json[$key])) {
            return $json[$key];
        }

        // POSTパラメータ
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        // GETパラメータ
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }

    /**
     * 全パラメータ取得
     */
    public static function all(): array
    {
        return array_merge($_GET, $_POST, self::json());
    }

    /**
     * 指定キーのみ取得
     */
    public static function only(array $keys): array
    {
        $all = self::all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * バリデーション
     */
    public static function validate(array $rules): array
    {
        $errors = [];
        $data = self::all();

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $rule);

            foreach ($ruleList as $r) {
                $error = self::checkRule($field, $value, $r);
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }

        return $errors;
    }

    private static function checkRule(string $field, $value, string $rule): ?string
    {
        // required
        if ($rule === 'required') {
            if ($value === null || $value === '') {
                return "{$field}は必須です";
            }
        }

        // integer
        if ($rule === 'integer') {
            if ($value !== null && $value !== '' && !is_numeric($value)) {
                return "{$field}は数値で入力してください";
            }
        }

        // min:n
        if (strpos($rule, 'min:') === 0) {
            $min = (int)substr($rule, 4);
            if ($value !== null && strlen($value) < $min) {
                return "{$field}は{$min}文字以上で入力してください";
            }
        }

        // max:n
        if (strpos($rule, 'max:') === 0) {
            $max = (int)substr($rule, 4);
            if ($value !== null && strlen($value) > $max) {
                return "{$field}は{$max}文字以下で入力してください";
            }
        }

        // date
        if ($rule === 'date') {
            if ($value !== null && $value !== '') {
                $d = DateTime::createFromFormat('Y-m-d', $value);
                if (!$d || $d->format('Y-m-d') !== $value) {
                    return "{$field}は有効な日付形式で入力してください";
                }
            }
        }

        return null;
    }
}
