<?php
if (!class_exists('ChangeRequest')) {
    /**
     * 変更リクエストモデル
     */
    class ChangeRequest
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
                "SELECT * FROM change_requests WHERE id = ?",
                [$id]
            );
        }

        /**
         * 申し込みIDで取得（全リクエスト）
         */
        public function getByApplicationId(int $applicationId): array
        {
            return $this->db->fetchAll(
                "SELECT cr.*, m.name_kanji, m.name_kana
                 FROM change_requests cr
                 JOIN members m ON cr.member_id = m.id
                 WHERE cr.application_id = ?
                 ORDER BY cr.created_at DESC",
                [$applicationId]
            );
        }

        /**
         * 承認待ちのリクエストを取得
         */
        public function getPending(int $applicationId = null): array
        {
            if ($applicationId) {
                return $this->db->fetchAll(
                    "SELECT cr.*, m.name_kanji, m.name_kana,
                            ca.camp_id, c.name as camp_name
                     FROM change_requests cr
                     JOIN members m ON cr.member_id = m.id
                     JOIN camp_applications ca ON cr.application_id = ca.id
                     JOIN camps c ON ca.camp_id = c.id
                     WHERE cr.application_id = ? AND cr.status = 'pending'
                     ORDER BY cr.created_at DESC",
                    [$applicationId]
                );
            } else {
                // 全ての承認待ちリクエスト
                return $this->db->fetchAll(
                    "SELECT cr.*, m.name_kanji, m.name_kana,
                            ca.camp_id, c.name as camp_name
                     FROM change_requests cr
                     JOIN members m ON cr.member_id = m.id
                     JOIN camp_applications ca ON cr.application_id = ca.id
                     JOIN camps c ON ca.camp_id = c.id
                     WHERE cr.status = 'pending'
                     ORDER BY cr.created_at DESC"
                );
            }
        }

        /**
         * 合宿IDで承認待ちリクエストを取得
         */
        public function getPendingByCampId(int $campId): array
        {
            return $this->db->fetchAll(
                "SELECT cr.*, m.name_kanji, m.name_kana,
                        ca.camp_id, c.name as camp_name
                 FROM change_requests cr
                 JOIN members m ON cr.member_id = m.id
                 JOIN camp_applications ca ON cr.application_id = ca.id
                 JOIN camps c ON ca.camp_id = c.id
                 WHERE ca.camp_id = ? AND cr.status = 'pending'
                 ORDER BY cr.created_at DESC",
                [$campId]
            );
        }

        /**
         * 新規作成
         */
        public function create(array $data): int
        {
            $sql = "INSERT INTO change_requests (
                application_id, member_id, change_type, old_value, new_value, reason, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            return $this->db->insert($sql, [
                $data['application_id'],
                $data['member_id'],
                $data['change_type'],
                $data['old_value'] ?? null,
                $data['new_value'],
                $data['reason'] ?? null,
                $data['status'] ?? 'pending',
            ]);
        }

        /**
         * 承認処理
         */
        public function approve(int $id, string $reviewedBy = null): bool
        {
            $request = $this->find($id);
            if (!$request || $request['status'] !== 'pending') {
                return false;
            }

            $this->db->beginTransaction();

            try {
                // 1. リクエストステータスを承認に変更
                $this->db->execute(
                    "UPDATE change_requests SET status = 'approved', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?",
                    [$reviewedBy, $id]
                );

                // 2. 変更内容を申し込みテーブルに反映
                $newValue = json_decode($request['new_value'], true);

                if ($request['change_type'] === 'cancel') {
                    // キャンセルの場合
                    $applicationModel = new CampApplication();
                    $applicationModel->cancelApplication($request['application_id']);
                } else {
                    // スケジュール・交通手段の変更
                    $applicationModel = new CampApplication();
                    $updateData = [];

                    if ($request['change_type'] === 'schedule') {
                        $updateData = [
                            'join_day' => $newValue['join_day'] ?? null,
                            'join_timing' => $newValue['join_timing'] ?? null,
                            'leave_day' => $newValue['leave_day'] ?? null,
                            'leave_timing' => $newValue['leave_timing'] ?? null,
                        ];
                    } elseif ($request['change_type'] === 'transport') {
                        $updateData = [
                            'use_outbound_bus' => $newValue['use_outbound_bus'] ?? null,
                            'use_return_bus' => $newValue['use_return_bus'] ?? null,
                        ];
                    }

                    // NULLを除外
                    $updateData = array_filter($updateData, function($value) {
                        return $value !== null;
                    });

                    $applicationModel->update($request['application_id'], $updateData);

                    // 参加者テーブルも更新
                    $application = $applicationModel->find($request['application_id']);
                    if ($application && $application['participant_id']) {
                        $participantModel = new Participant();
                        $participantModel->update($application['participant_id'], $updateData);
                    }
                }

                $this->db->commit();
                return true;

            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        }

        /**
         * 却下処理
         */
        public function reject(int $id, string $reviewedBy = null): bool
        {
            $request = $this->find($id);
            if (!$request || $request['status'] !== 'pending') {
                return false;
            }

            return $this->db->execute(
                "UPDATE change_requests SET status = 'rejected', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?",
                [$reviewedBy, $id]
            ) > 0;
        }

        /**
         * リクエスト数をカウント
         */
        public function countPending(int $campId = null): int
        {
            if ($campId) {
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count
                     FROM change_requests cr
                     JOIN camp_applications ca ON cr.application_id = ca.id
                     WHERE ca.camp_id = ? AND cr.status = 'pending'",
                    [$campId]
                );
            } else {
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM change_requests WHERE status = 'pending'"
                );
            }

            return $result['count'] ?? 0;
        }

        /**
         * 変更リクエストの作成（申し込み変更用ヘルパー）
         */
        public function createScheduleChange(int $applicationId, int $memberId, array $oldSchedule, array $newSchedule, ?string $reason = null): int
        {
            return $this->create([
                'application_id' => $applicationId,
                'member_id' => $memberId,
                'change_type' => 'schedule',
                'old_value' => json_encode($oldSchedule, JSON_UNESCAPED_UNICODE),
                'new_value' => json_encode($newSchedule, JSON_UNESCAPED_UNICODE),
                'reason' => $reason,
            ]);
        }

        /**
         * 交通手段変更リクエストの作成
         */
        public function createTransportChange(int $applicationId, int $memberId, array $oldTransport, array $newTransport, ?string $reason = null): int
        {
            return $this->create([
                'application_id' => $applicationId,
                'member_id' => $memberId,
                'change_type' => 'transport',
                'old_value' => json_encode($oldTransport, JSON_UNESCAPED_UNICODE),
                'new_value' => json_encode($newTransport, JSON_UNESCAPED_UNICODE),
                'reason' => $reason,
            ]);
        }

        /**
         * キャンセルリクエストの作成
         */
        public function createCancelRequest(int $applicationId, int $memberId, ?string $reason = null): int
        {
            // old_valueには現在の申し込み内容を保存
            $applicationModel = new CampApplication();
            $application = $applicationModel->find($applicationId);

            $oldValue = [
                'join_day' => $application['join_day'],
                'join_timing' => $application['join_timing'],
                'leave_day' => $application['leave_day'],
                'leave_timing' => $application['leave_timing'],
                'use_outbound_bus' => $application['use_outbound_bus'],
                'use_return_bus' => $application['use_return_bus'],
            ];

            return $this->create([
                'application_id' => $applicationId,
                'member_id' => $memberId,
                'change_type' => 'cancel',
                'old_value' => json_encode($oldValue, JSON_UNESCAPED_UNICODE),
                'new_value' => json_encode(['cancelled' => true], JSON_UNESCAPED_UNICODE),
                'reason' => $reason,
            ]);
        }
    }
}
