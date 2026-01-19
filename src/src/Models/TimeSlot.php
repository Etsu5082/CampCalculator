<?php
/**
 * タイムスロットモデル
 */
class TimeSlot
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
            "SELECT * FROM time_slots WHERE camp_id = ? ORDER BY day_number,
             FIELD(slot_type, 'outbound', 'morning', 'afternoon', 'banquet', 'return')",
            [$campId]
        );
    }

    /**
     * ID指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM time_slots WHERE id = ?",
            [$id]
        );
    }

    /**
     * 新規作成
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO time_slots (
            camp_id, day_number, slot_type, activity_type, facility_fee, court_count, description
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['camp_id'],
            $data['day_number'],
            $data['slot_type'],
            $data['activity_type'] ?? null,
            $data['facility_fee'] ?? null,
            $data['court_count'] ?? 1,
            $data['description'] ?? null,
        ]);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = ['activity_type', 'facility_fee', 'court_count', 'description'];

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
        $sql = "UPDATE time_slots SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->execute($sql, $values) > 0;
    }

    /**
     * 削除
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM time_slots WHERE id = ?", [$id]) > 0;
    }

    /**
     * 合宿のタイムスロットを一括更新
     */
    public function updateByCampId(int $campId, array $slots): void
    {
        // 既存のスロットを削除
        $this->db->execute("DELETE FROM time_slots WHERE camp_id = ?", [$campId]);

        // 新しいスロットを挿入
        foreach ($slots as $slot) {
            $slot['camp_id'] = $campId;
            $this->create($slot);
        }
    }

    /**
     * デフォルトのタイムスロットを生成
     * @param int $campId 合宿ID
     * @param int $nights 泊数
     * @param int|null $courtFeePerUnit コート単価（テニス用）
     * @param int|null $gymFeePerUnit 体育館単価
     */
    public function createDefaultSlots(int $campId, int $nights, ?int $courtFeePerUnit = null, ?int $gymFeePerUnit = null): void
    {
        $days = $nights + 1;
        // デフォルトのコート数は1
        $defaultCourtCount = 1;
        // テニスのfacility_feeを計算（コート単価 × コート数）
        $tennisFee = $courtFeePerUnit ? $courtFeePerUnit * $defaultCourtCount : 0;

        for ($day = 1; $day <= $days; $day++) {
            // 1日目は往路
            if ($day === 1) {
                $this->create([
                    'camp_id' => $campId,
                    'day_number' => $day,
                    'slot_type' => 'outbound',
                    'activity_type' => 'bus',
                    'description' => 'バス移動（往路）',
                ]);
            }

            // 最終日以外は午前スロット
            if ($day > 1) {
                $this->create([
                    'camp_id' => $campId,
                    'day_number' => $day,
                    'slot_type' => 'morning',
                    'activity_type' => 'tennis',
                    'facility_fee' => $tennisFee,
                    'court_count' => $defaultCourtCount,
                    'description' => 'テニスコート',
                ]);
            }

            // 午後スロット
            if ($day < $days) {
                $this->create([
                    'camp_id' => $campId,
                    'day_number' => $day,
                    'slot_type' => 'afternoon',
                    'activity_type' => 'tennis',
                    'facility_fee' => $tennisFee,
                    'court_count' => $defaultCourtCount,
                    'description' => 'テニスコート',
                ]);
            }

            // 最終日は復路
            if ($day === $days) {
                $this->create([
                    'camp_id' => $campId,
                    'day_number' => $day,
                    'slot_type' => 'return',
                    'activity_type' => 'bus',
                    'description' => 'バス移動（復路）',
                ]);
            }
        }
    }

    /**
     * 合宿複製用
     */
    public function duplicateForCamp(int $originalCampId, int $newCampId): void
    {
        $slots = $this->getByCampId($originalCampId);

        foreach ($slots as $slot) {
            unset($slot['id']);
            $slot['camp_id'] = $newCampId;
            $this->create($slot);
        }
    }
}
