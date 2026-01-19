<?php
/**
 * 雑費モデル
 */
class Expense
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
            "SELECT e.*, p.name as payer_name
             FROM expenses e
             LEFT JOIN participants p ON e.payer_id = p.id
             WHERE e.camp_id = ?
             ORDER BY e.id",
            [$campId]
        );
    }

    /**
     * ID指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM expenses WHERE id = ?",
            [$id]
        );
    }

    /**
     * 新規作成
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO expenses (
            camp_id, name, amount, target_type, target_day, target_slot, payer_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['camp_id'],
            $data['name'],
            $data['amount'],
            $data['target_type'] ?? 'all',
            $data['target_day'] ?? null,
            $data['target_slot'] ?? null,
            $data['payer_id'] ?? null,
        ]);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = ['name', 'amount', 'target_type', 'target_day', 'target_slot', 'payer_id'];

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
        $sql = "UPDATE expenses SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->execute($sql, $values) > 0;
    }

    /**
     * 削除
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM expenses WHERE id = ?", [$id]) > 0;
    }

    /**
     * 合宿複製用
     */
    public function duplicateForCamp(int $originalCampId, int $newCampId): void
    {
        $expenses = $this->getByCampId($originalCampId);

        foreach ($expenses as $expense) {
            unset($expense['id']);
            $expense['camp_id'] = $newCampId;
            $this->create($expense);
        }
    }
}
