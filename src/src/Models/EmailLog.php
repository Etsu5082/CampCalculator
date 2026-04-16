<?php
if (!class_exists('EmailLog')) {
    /**
     * メール送信ログモデル
     */
    class EmailLog
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
                "SELECT * FROM email_logs WHERE id = ?",
                [$id]
            );
        }

        /**
         * 会員IDで取得
         */
        public function getByMemberId(int $memberId): array
        {
            return $this->db->fetchAll(
                "SELECT * FROM email_logs WHERE member_id = ? ORDER BY created_at DESC",
                [$memberId]
            );
        }

        /**
         * メール種別で取得
         */
        public function getByType(string $emailType, int $limit = 100): array
        {
            return $this->db->fetchAll(
                "SELECT * FROM email_logs WHERE email_type = ? ORDER BY created_at DESC LIMIT ?",
                [$emailType, $limit]
            );
        }

        /**
         * ステータスで取得
         */
        public function getByStatus(string $status, int $limit = 100): array
        {
            return $this->db->fetchAll(
                "SELECT * FROM email_logs WHERE status = ? ORDER BY created_at DESC LIMIT ?",
                [$status, $limit]
            );
        }

        /**
         * 送信失敗のログを取得
         */
        public function getFailedLogs(int $limit = 100): array
        {
            return $this->db->fetchAll(
                "SELECT * FROM email_logs WHERE status = 'failed' ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );
        }

        /**
         * 新規作成
         */
        public function create(array $data): int
        {
            $sql = "INSERT INTO email_logs (
                member_id, email_type, to_address, subject, body, status, sent_at, error_message
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            return $this->db->insert($sql, [
                $data['member_id'] ?? null,
                $data['email_type'],
                $data['to_address'],
                $data['subject'],
                $data['body'],
                $data['status'] ?? 'pending',
                $data['sent_at'] ?? null,
                $data['error_message'] ?? null,
            ]);
        }

        /**
         * ステータス更新
         */
        public function updateStatus(int $id, string $status, ?string $errorMessage = null): bool
        {
            $sql = "UPDATE email_logs SET status = ?, error_message = ?";
            $params = [$status, $errorMessage];

            // 送信成功の場合は送信日時も更新
            if ($status === 'sent') {
                $sql .= ", sent_at = NOW()";
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            return $this->db->execute($sql, $params) > 0;
        }

        /**
         * 送信成功としてマーク
         */
        public function markAsSent(int $id): bool
        {
            return $this->updateStatus($id, 'sent');
        }

        /**
         * 送信失敗としてマーク
         */
        public function markAsFailed(int $id, string $errorMessage): bool
        {
            return $this->updateStatus($id, 'failed', $errorMessage);
        }

        /**
         * メール送信とログ記録を同時に実行
         */
        public function sendAndLog(array $emailData): array
        {
            // ログを先に作成
            $logId = $this->create([
                'member_id' => $emailData['member_id'] ?? null,
                'email_type' => $emailData['email_type'],
                'to_address' => $emailData['to_address'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'status' => 'pending',
            ]);

            try {
                // メール送信処理（実装は環境に応じて変更）
                $result = $this->sendEmail(
                    $emailData['to_address'],
                    $emailData['subject'],
                    $emailData['body'],
                    $emailData['headers'] ?? []
                );

                if ($result) {
                    $this->markAsSent($logId);
                    return ['success' => true, 'log_id' => $logId];
                } else {
                    $this->markAsFailed($logId, 'メール送信に失敗しました');
                    return ['success' => false, 'log_id' => $logId, 'error' => 'メール送信に失敗しました'];
                }

            } catch (Exception $e) {
                $this->markAsFailed($logId, $e->getMessage());
                return ['success' => false, 'log_id' => $logId, 'error' => $e->getMessage()];
            }
        }

        /**
         * メール送信処理（mb_sendまたはSMTPライブラリを使用）
         */
        private function sendEmail(string $to, string $subject, string $body, array $headers = []): bool
        {
            // デフォルトヘッダー
            $defaultHeaders = [
                'From: noreply@example.com',
                'Content-Type: text/plain; charset=UTF-8',
            ];

            $allHeaders = array_merge($defaultHeaders, $headers);
            $headerString = implode("\r\n", $allHeaders);

            // mb_send_mailを使用（環境に応じてSMTPライブラリに変更）
            mb_language('ja');
            mb_internal_encoding('UTF-8');

            return mb_send_mail($to, $subject, $body, $headerString);
        }

        /**
         * 統計情報取得
         */
        public function getStats(int $days = 30): array
        {
            $sql = "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                    FROM email_logs
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";

            return $this->db->fetch($sql, [$days]) ?? [
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
                'pending' => 0,
            ];
        }

        /**
         * 最近のログ取得（ダッシュボード用）
         */
        public function getRecentLogs(int $limit = 20): array
        {
            return $this->db->fetchAll(
                "SELECT el.*, m.name_kanji
                 FROM email_logs el
                 LEFT JOIN members m ON el.member_id = m.id
                 ORDER BY el.created_at DESC
                 LIMIT ?",
                [$limit]
            );
        }

        /**
         * 特定期間のメール送信数を取得
         */
        public function countByDateRange(string $startDate, string $endDate, ?string $status = null): int
        {
            if ($status) {
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count
                     FROM email_logs
                     WHERE created_at BETWEEN ? AND ? AND status = ?",
                    [$startDate, $endDate, $status]
                );
            } else {
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count
                     FROM email_logs
                     WHERE created_at BETWEEN ? AND ?",
                    [$startDate, $endDate]
                );
            }

            return $result['count'] ?? 0;
        }

        /**
         * 古いログを削除（定期クリーンアップ用）
         */
        public function deleteOldLogs(int $days = 365): int
        {
            return $this->db->execute(
                "DELETE FROM email_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            );
        }
    }
}
