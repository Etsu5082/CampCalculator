-- コート1面あたり料金と面数を追加するマイグレーション

-- campsテーブルにコート1面あたり料金を追加
ALTER TABLE camps
    ADD COLUMN court_fee_per_unit INT DEFAULT NULL COMMENT 'コート1面あたり料金' AFTER insurance_fee;

-- time_slotsテーブルにコート面数を追加
ALTER TABLE time_slots
    ADD COLUMN court_count INT DEFAULT 1 COMMENT '使用コート面数' AFTER facility_fee;
