-- 宴会場1人あたり料金カラムを追加
ALTER TABLE camps ADD COLUMN banquet_fee_per_person INT DEFAULT NULL AFTER gym_fee_per_unit;
