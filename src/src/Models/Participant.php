<?php
/**
 * 参加者モデル
 */
class Participant
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 合宿IDで取得
     */
    public function getByCampId(int $campId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM participants WHERE camp_id = ? ORDER BY name",
            [$campId]
        );
    }

    /**
     * ID指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM participants WHERE id = ?",
            [$id]
        );
    }

    /**
     * 新規作成
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO participants (
            camp_id, name, grade, gender, join_day, join_timing, leave_day, leave_timing,
            use_outbound_bus, use_return_bus, use_rental_car
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // 合宿の総日数を取得（leave_timingのデフォルト値判定に使用）
        $camp = (new Camp())->find($data['camp_id']);
        $totalDays = $camp ? $camp['nights'] + 1 : 4;

        // join_dayのデフォルト値
        $joinDay = $data['join_day'] ?? 1;
        $leaveDay = $data['leave_day'] ?? $totalDays;

        // join_timingのデフォルト値（1日目は往路バス、それ以外は午前イベント）
        $defaultJoinTiming = ($joinDay === 1) ? 'outbound_bus' : 'morning';
        $joinTiming = $data['join_timing'] ?? $defaultJoinTiming;

        // leave_timingのデフォルト値（最終日は復路バス、それ以外は昼食後）
        $defaultLeaveTiming = ($leaveDay === $totalDays) ? 'return_bus' : 'after_lunch';
        $leaveTiming = $data['leave_timing'] ?? $defaultLeaveTiming;

        $participantId = $this->db->insert($sql, [
            $data['camp_id'],
            $data['name'],
            $data['grade'] ?? null,
            $data['gender'] ?? null,
            $joinDay,
            $joinTiming,
            $leaveDay,
            $leaveTiming,
            $data['use_outbound_bus'] ?? 1,
            $data['use_return_bus'] ?? 1,
            $data['use_rental_car'] ?? 0,
        ]);

        // 参加スロットと食事調整を自動生成
        $this->generateParticipantSlots($participantId);
        $this->generateMealAdjustments($participantId);

        return $participantId;
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = [
            'name', 'grade', 'gender', 'join_day', 'join_timing', 'leave_day', 'leave_timing',
            'use_outbound_bus', 'use_return_bus', 'use_rental_car',
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
        $sql = "UPDATE participants SET " . implode(', ', $fields) . " WHERE id = ?";

        $result = $this->db->execute($sql, $values) > 0;

        // 参加スロットと食事調整を再生成
        if ($result) {
            $this->generateParticipantSlots($id);
            $this->generateMealAdjustments($id);
        }

        return $result;
    }

    /**
     * 削除
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM participants WHERE id = ?", [$id]) > 0;
    }

    /**
     * 参加スロットを自動生成
     */
    private function generateParticipantSlots(int $participantId): void
    {
        $participant = $this->find($participantId);
        if (!$participant) {
            return;
        }

        // 既存のスロットを削除
        $this->db->execute(
            "DELETE FROM participant_slots WHERE participant_id = ?",
            [$participantId]
        );

        // タイムスロットを取得
        $timeSlotModel = new TimeSlot();
        $slots = $timeSlotModel->getByCampId($participant['camp_id']);

        foreach ($slots as $slot) {
            $isAttending = $this->checkSlotAttendance($participant, $slot);

            $this->db->execute(
                "INSERT INTO participant_slots (participant_id, time_slot_id, is_attending)
                 VALUES (?, ?, ?)",
                [$participantId, $slot['id'], $isAttending ? 1 : 0]
            );
        }
    }

    /**
     * スロットへの参加チェック
     */
    private function checkSlotAttendance(array $participant, array $slot): bool
    {
        $joinDay = $participant['join_day'];
        $leaveDay = $participant['leave_day'];
        $joinTiming = $participant['join_timing'];
        $leaveTiming = $participant['leave_timing'];
        $slotDay = $slot['day_number'];
        $slotType = $slot['slot_type'];

        // 参加期間外は不参加
        if ($slotDay < $joinDay || $slotDay > $leaveDay) {
            return false;
        }

        // 参加開始日のチェック
        if ($slotDay === $joinDay) {
            $joinOrder = $this->getTimingOrder($joinTiming);
            $slotOrder = $this->getSlotOrder($slotType);

            if ($slotOrder < $joinOrder) {
                return false;
            }
        }

        // 離脱日のチェック
        if ($slotDay === $leaveDay) {
            $leaveOrder = $this->getLeaveTimingOrder($leaveTiming);
            $slotOrder = $this->getSlotOrder($slotType);

            if ($slotOrder > $leaveOrder) {
                return false;
            }
        }

        // 往路バス
        if ($slotType === 'outbound' && !$participant['use_outbound_bus']) {
            return false;
        }

        // 復路バス
        if ($slotType === 'return' && !$participant['use_return_bus']) {
            return false;
        }

        return true;
    }

    /**
     * 参加タイミングの順序
     */
    private function getTimingOrder(string $timing): int
    {
        $order = [
            'outbound_bus' => 0,  // 往路バスから（1日目のみ）
            'morning' => 1,
            'lunch' => 2,
            'afternoon' => 3,
            'dinner' => 4,
            'night' => 5,
        ];
        return $order[$timing] ?? 0;
    }

    /**
     * 離脱タイミングの順序
     */
    private function getLeaveTimingOrder(string $timing): int
    {
        $order = [
            'morning' => 0,
            'after_breakfast' => 1,
            'lunch' => 2,
            'after_lunch' => 3,
            'afternoon' => 4,
            'dinner' => 5,
            'night' => 6,
        ];
        return $order[$timing] ?? 3;
    }

    /**
     * スロットタイプの順序
     */
    private function getSlotOrder(string $slotType): int
    {
        $order = [
            'outbound' => 1,
            'morning' => 2,
            'afternoon' => 3,
            'banquet' => 4,
            'return' => 3, // 午後と同等
        ];
        return $order[$slotType] ?? 2;
    }

    /**
     * 食事調整を自動生成
     */
    private function generateMealAdjustments(int $participantId): void
    {
        $participant = $this->find($participantId);
        if (!$participant) {
            return;
        }

        // 既存の調整を削除
        $this->db->execute(
            "DELETE FROM meal_adjustments WHERE participant_id = ?",
            [$participantId]
        );

        $camp = (new Camp())->find($participant['camp_id']);
        if (!$camp) {
            return;
        }

        $joinDay = $participant['join_day'];
        $joinTiming = $participant['join_timing'];
        $leaveDay = $participant['leave_day'];
        $leaveTiming = $participant['leave_timing'];
        $days = $camp['nights'] + 1;

        // 参加日の食事調整
        if ($joinTiming === 'lunch') {
            // 昼食から参加 → 昼食追加
            $this->addMealAdjustment($participantId, $joinDay, 'lunch', 'add');
        } elseif ($joinTiming === 'night') {
            // 夕食後から参加 → 夕食欠食
            $this->addMealAdjustment($participantId, $joinDay, 'dinner', 'remove');
        }

        // 参加日より前の日の食事は欠食（1日目の昼食は除く）
        for ($day = 1; $day < $joinDay; $day++) {
            if ($day > 1 || $camp['first_day_lunch_included']) {
                // 1日目以外、または1日目昼食が対象の場合
            }
            // 全ての食事を欠食
            $this->addMealAdjustment($participantId, $day, 'dinner', 'remove');
            if ($day < $days) {
                $this->addMealAdjustment($participantId, $day + 1, 'breakfast', 'remove');
                $this->addMealAdjustment($participantId, $day + 1, 'lunch', 'remove');
            }
        }

        // 離脱日の食事調整
        if ($leaveTiming === 'morning' || $leaveTiming === 'after_breakfast') {
            // 朝食後に離脱 → 昼食欠食
            $this->addMealAdjustment($participantId, $leaveDay, 'lunch', 'remove');
        }

        // 離脱日より後の日の食事は欠食
        for ($day = $leaveDay + 1; $day <= $days; $day++) {
            $this->addMealAdjustment($participantId, $day, 'breakfast', 'remove');
            $this->addMealAdjustment($participantId, $day, 'lunch', 'remove');
            if ($day < $days) {
                $this->addMealAdjustment($participantId, $day, 'dinner', 'remove');
            }
        }
    }

    /**
     * 食事調整レコード追加
     */
    private function addMealAdjustment(int $participantId, int $dayNumber, string $mealType, string $adjustmentType): void
    {
        $this->db->execute(
            "INSERT IGNORE INTO meal_adjustments (participant_id, day_number, meal_type, adjustment_type)
             VALUES (?, ?, ?, ?)",
            [$participantId, $dayNumber, $mealType, $adjustmentType]
        );
    }

    /**
     * 参加者の参加スロット取得
     */
    public function getParticipantSlots(int $participantId): array
    {
        return $this->db->fetchAll(
            "SELECT ps.*, ts.day_number, ts.slot_type, ts.activity_type, ts.facility_fee, ts.description
             FROM participant_slots ps
             JOIN time_slots ts ON ps.time_slot_id = ts.id
             WHERE ps.participant_id = ?
             ORDER BY ts.day_number, FIELD(ts.slot_type, 'outbound', 'morning', 'afternoon', 'banquet', 'return')",
            [$participantId]
        );
    }

    /**
     * 参加者の食事調整取得
     */
    public function getMealAdjustments(int $participantId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM meal_adjustments WHERE participant_id = ? ORDER BY day_number, meal_type",
            [$participantId]
        );
    }

    /**
     * 参加者数をカウント（合宿ID指定）
     */
    public function countByCampId(int $campId): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM participants WHERE camp_id = ?",
            [$campId]
        );
        return $result['count'] ?? 0;
    }

    /**
     * CSVから一括登録
     */
    public function bulkCreateFromCsv(int $campId, array $rows): array
    {
        $camp = (new Camp())->find($campId);
        if (!$camp) {
            return ['success' => 0, 'errors' => ['合宿が見つかりません']];
        }

        $days = $camp['nights'] + 1;
        $success = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $lineNum = $index + 1;

            // 空行スキップ
            if (empty($row[0])) {
                continue;
            }

            $name = trim($row[0]);
            $gradeGender = isset($row[1]) ? trim($row[1]) : '';

            // 学年・性別をパース (1男、1女、2男、2女、3男、3女、OB、OG、OBOG)
            // 全角数字を半角に変換
            $gradeGender = mb_convert_kana($gradeGender, 'n');
            $grade = null;
            $gender = null;

            if (preg_match('/^([1-4])([男女])$/u', $gradeGender, $matches)) {
                $grade = (int)$matches[1];
                $gender = $matches[2] === '男' ? 'male' : 'female';
            } elseif (preg_match('/^([1-4])年([男女])$/u', $gradeGender, $matches)) {
                // 「1年男」「2年女」などの形式にも対応
                $grade = (int)$matches[1];
                $gender = $matches[2] === '男' ? 'male' : 'female';
            } elseif (strtoupper($gradeGender) === 'OB') {
                $grade = 0;
                $gender = 'male';
            } elseif (strtoupper($gradeGender) === 'OG') {
                $grade = 0;
                $gender = 'female';
            } elseif (strtoupper($gradeGender) === 'OBOG') {
                $grade = 0;
                $gender = null;
            }

            try {
                $this->create([
                    'camp_id' => $campId,
                    'name' => $name,
                    'grade' => $grade,
                    'gender' => $gender,
                    'join_day' => 1,
                    'join_timing' => 'outbound_bus',  // 1日目は往路バスから
                    'leave_day' => $days,
                    'leave_timing' => 'return_bus',   // 最終日は復路バスまで
                    'use_outbound_bus' => 1,
                    'use_return_bus' => 1,
                    'use_rental_car' => 0,
                ]);
                $success++;
            } catch (Exception $e) {
                $errors[] = "{$lineNum}行目: {$name} の登録に失敗しました";
            }
        }

        return ['success' => $success, 'errors' => $errors];
    }
}
