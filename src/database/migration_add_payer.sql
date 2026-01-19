-- 雑費テーブルに建て替え者カラムを追加
ALTER TABLE expenses
    ADD COLUMN payer_id INT DEFAULT NULL AFTER target_slot,
    ADD CONSTRAINT fk_expenses_payer FOREIGN KEY (payer_id) REFERENCES participants(id) ON DELETE SET NULL;

SELECT 'マイグレーション完了: 雑費に建て替え者カラムを追加しました' AS message;
