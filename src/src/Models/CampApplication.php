<?php
if (!class_exists('CampApplication')) {
    /**
     * 合宿申し込みモデル
     */
    class CampApplication
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
                "SELECT * FROM camp_applications WHERE id = ?",
                [$id]
            );
        }

        /**
         * 合宿IDと会員IDで取得（キャンセル済みは除く）
         */
        public function findByCampAndMember(int $campId, int $memberId): ?array
        {
            return $this->db->fetch(
                "SELECT * FROM camp_applications WHERE camp_id = ? AND member_id = ? AND status != 'cancelled'",
                [$campId, $memberId]
            );
        }

        /**
         * 合宿IDで取得（全申し込み）
         */
        public function getByCampId(int $campId): array
        {
            return $this->db->fetchAll(
                "SELECT ca.*, m.name_kanji, m.name_kana, m.grade as member_grade, m.gender
                 FROM camp_applications ca
                 JOIN members m ON ca.member_id = m.id
                 WHERE ca.camp_id = ?
                 ORDER BY ca.created_at DESC",
                [$campId]
            );
        }

        /**
         * 会員IDで取得（全申し込み履歴）
         */
        public function getByMemberId(int $memberId): array
        {
            return $this->db->fetchAll(
                "SELECT ca.*, c.name as camp_name, c.start_date, c.end_date
                 FROM camp_applications ca
                 JOIN camps c ON ca.camp_id = c.id
                 WHERE ca.member_id = ?
                 ORDER BY c.start_date DESC",
                [$memberId]
            );
        }

        /**
         * ステータスで絞り込み
         */
        public function getByCampIdAndStatus(int $campId, string $status): array
        {
            return $this->db->fetchAll(
                "SELECT ca.*, m.name_kanji, m.name_kana, m.grade as member_grade, m.gender
                 FROM camp_applications ca
                 JOIN members m ON ca.member_id = m.id
                 WHERE ca.camp_id = ? AND ca.status = ?
                 ORDER BY ca.created_at DESC",
                [$campId, $status]
            );
        }

        /**
         * 新規作成
         */
        public function create(array $data): int
        {
            $sql = "INSERT INTO camp_applications (
                camp_id, member_id, participant_id, join_day, join_timing, leave_day, leave_timing,
                use_outbound_bus, use_return_bus, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // 合宿の総日数を取得
            $camp = (new Camp())->find($data['camp_id']);
            $totalDays = $camp ? $camp['nights'] + 1 : 4;

            return $this->db->insert($sql, [
                $data['camp_id'],
                $data['member_id'],
                $data['participant_id'] ?? null,
                $data['join_day'] ?? 1,
                $data['join_timing'] ?? 'outbound_bus',
                $data['leave_day'] ?? $totalDays,
                $data['leave_timing'] ?? 'return_bus',
                $data['use_outbound_bus'] ?? 1,
                $data['use_return_bus'] ?? 1,
                $data['status'] ?? 'submitted',
            ]);
        }

        /**
         * 更新
         */
        public function update(int $id, array $data): bool
        {
            $fields = [];
            $values = [];

            $allowedFields = [
                'participant_id', 'join_day', 'join_timing', 'leave_day', 'leave_timing',
                'use_outbound_bus', 'use_return_bus', 'status'
            ];

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
            $sql = "UPDATE camp_applications SET " . implode(', ', $fields) . " WHERE id = ?";

            return $this->db->execute($sql, $values) > 0;
        }

        /**
         * 削除
         */
        public function delete(int $id): bool
        {
            return $this->db->execute("DELETE FROM camp_applications WHERE id = ?", [$id]) > 0;
        }

        /**
         * 申し込みと同時に参加者テーブルにレコード作成
         */
        public function createWithParticipant(array $applicationData): array
        {
            $this->db->beginTransaction();

            try {
                // 1. 会員情報を取得
                $member = $this->db->fetch(
                    "SELECT * FROM members WHERE id = ?",
                    [$applicationData['member_id']]
                );

                if (!$member) {
                    throw new Exception('会員が見つかりません');
                }

                // 2. 参加者テーブルにレコード作成
                $participantModel = new Participant();

                // 学年の変換（会員マスタのgradeをparticipantsのgradeに変換）
                $participantGrade = null;
                if (in_array($member['grade'], ['1', '2', '3', '4'])) {
                    $participantGrade = (int)$member['grade'];
                } elseif (in_array($member['grade'], ['OB', 'OG'])) {
                    $participantGrade = 0;
                }

                $participantId = $participantModel->create([
                    'camp_id' => $applicationData['camp_id'],
                    'name' => $member['name_kanji'],
                    'grade' => $participantGrade,
                    'gender' => $member['gender'],
                    'join_day' => $applicationData['join_day'] ?? 1,
                    'join_timing' => $applicationData['join_timing'] ?? 'outbound_bus',
                    'leave_day' => $applicationData['leave_day'],
                    'leave_timing' => $applicationData['leave_timing'] ?? 'return_bus',
                    'use_outbound_bus' => $applicationData['use_outbound_bus'] ?? 1,
                    'use_return_bus' => $applicationData['use_return_bus'] ?? 1,
                    'use_rental_car' => $applicationData['use_rental_car'] ?? 0,
                ]);

                // 3. 申し込みテーブルにレコード作成（participant_idを紐付け）
                $applicationData['participant_id'] = $participantId;
                $applicationId = $this->create($applicationData);

                $this->db->commit();

                return [
                    'success' => true,
                    'application_id' => $applicationId,
                    'participant_id' => $participantId,
                ];

            } catch (Exception $e) {
                $this->db->rollback();
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        /**
         * 申し込みをキャンセル（参加者も削除）
         */
        public function cancelApplication(int $id): bool
        {
            $application = $this->find($id);
            if (!$application) {
                return false;
            }

            $this->db->beginTransaction();

            try {
                // 1. ステータスをキャンセルに変更
                $this->update($id, ['status' => 'cancelled']);

                // 2. 参加者を削除（participant_idがあれば）
                if ($application['participant_id']) {
                    // 雑費の建て替え者が削除対象参加者を指している場合はNULLにする
                    $this->db->execute(
                        "UPDATE expenses SET payer_id = NULL WHERE payer_id = ?",
                        [$application['participant_id']]
                    );
                    $participantModel = new Participant();
                    $participantModel->delete($application['participant_id']);
                }

                $this->db->commit();
                return true;

            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        }

        /**
         * 申し込み人数をカウント
         */
        public function countByCampId(int $campId, ?string $status = null): int
        {
            if ($status) {
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM camp_applications WHERE camp_id = ? AND status = ?",
                    [$campId, $status]
                );
            } else {
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM camp_applications WHERE camp_id = ?",
                    [$campId]
                );
            }

            return $result['count'] ?? 0;
        }

        /**
         * 会員が既に申し込み済みかチェック
         */
        public function hasApplied(int $campId, int $memberId): bool
        {
            $application = $this->findByCampAndMember($campId, $memberId);
            return $application !== null && $application['status'] !== 'cancelled';
        }
    }
}
