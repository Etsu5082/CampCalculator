<?php
if (!class_exists('CampToken')) {
    /**
     * 合宿申し込みURLトークンモデル
     */
    class CampToken
    {
        private Database $db;

        public function __construct()
        {
            $this->db = Database::getInstance();
        }

        /**
         * ID指定で取得
         */
        public function find(int $id): ?array
        {
            return $this->db->fetch(
                "SELECT * FROM camp_tokens WHERE id = ?",
                [$id]
            );
        }

        /**
         * トークン文字列で取得
         */
        public function findByToken(string $token): ?array
        {
            return $this->db->fetch(
                "SELECT * FROM camp_tokens WHERE token = ?",
                [$token]
            );
        }

        /**
         * 合宿IDで取得（複数可能性あり）
         */
        public function findByCampId(int $campId): array
        {
            return $this->db->fetchAll(
                "SELECT * FROM camp_tokens WHERE camp_id = ? ORDER BY created_at DESC",
                [$campId]
            );
        }

        /**
         * 有効なトークンを合宿IDで取得
         */
        public function findActiveByCampId(int $campId): ?array
        {
            return $this->db->fetch(
                "SELECT * FROM camp_tokens
                 WHERE camp_id = ? AND is_active = 1
                 AND (deadline IS NULL OR deadline > NOW())
                 ORDER BY created_at DESC
                 LIMIT 1",
                [$campId]
            );
        }

        /**
         * 新規作成
         */
        public function create(array $data): int
        {
            $sql = "INSERT INTO camp_tokens (
                camp_id, token, deadline, is_active
            ) VALUES (?, ?, ?, ?)";

            return $this->db->insert($sql, [
                $data['camp_id'],
                $data['token'] ?? $this->generateToken(),
                $data['deadline'] ?? null,
                $data['is_active'] ?? 1,
            ]);
        }

        /**
         * 更新
         */
        public function update(int $id, array $data): bool
        {
            $fields = [];
            $values = [];

            $allowedFields = ['deadline', 'is_active'];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $values[] = $id;
            $sql = "UPDATE camp_tokens SET " . implode(', ', $fields) . " WHERE id = ?";

            return $this->db->execute($sql, $values) > 0;
        }

        /**
         * 削除
         */
        public function delete(int $id): bool
        {
            return $this->db->execute("DELETE FROM camp_tokens WHERE id = ?", [$id]) > 0;
        }

        /**
         * トークン無効化
         */
        public function deactivate(int $id): bool
        {
            return $this->update($id, ['is_active' => 0]);
        }

        /**
         * ランダムトークン生成（64文字）
         */
        public function generateToken(): string
        {
            // 衝突回避のため、既存トークンをチェックしながら生成
            $maxAttempts = 10;
            $attempt = 0;

            do {
                $token = bin2hex(random_bytes(32)); // 64文字の16進数文字列
                $exists = $this->findByToken($token);
                $attempt++;
            } while ($exists && $attempt < $maxAttempts);

            if ($exists) {
                // 念のため現在時刻とマイクロ秒を混ぜる
                $token = hash('sha256', uniqid('', true) . random_bytes(32));
            }

            return $token;
        }

        /**
         * トークンの有効性チェック
         */
        public function isValid(string $token): bool
        {
            $tokenData = $this->findByToken($token);

            if (!$tokenData) {
                return false;
            }

            // 無効化されているか
            if (!$tokenData['is_active']) {
                return false;
            }

            // 締切チェック
            if ($tokenData['deadline'] && strtotime($tokenData['deadline']) < time()) {
                return false;
            }

            return true;
        }

        /**
         * 合宿の申し込みURL取得
         */
        public function getApplicationUrl(int $campId): ?string
        {
            $token = $this->findActiveByCampId($campId);

            if (!$token) {
                return null;
            }

            // ベースURLは環境に応じて変更
            $baseUrl = $_SERVER['REQUEST_SCHEME'] ?? 'https';
            $baseUrl .= '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

            return $baseUrl . '/application.php?token=' . $token['token'];
        }

        /**
         * 募集中の合宿一覧を取得（有効なトークンがある合宿）
         */
        public function getActiveCampsWithTokens(): array
        {
            return $this->db->fetchAll(
                "SELECT ct.*, c.name as camp_name, c.start_date, c.end_date
                 FROM camp_tokens ct
                 INNER JOIN camps c ON ct.camp_id = c.id
                 WHERE ct.is_active = 1
                 AND (ct.deadline IS NULL OR ct.deadline > NOW())
                 ORDER BY c.start_date ASC"
            );
        }
    }
}
