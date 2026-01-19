-- 既存データベースへのマイグレーション
-- バス往復料金の一括設定とレンタカーオプションの追加

-- campsテーブルに新しいカラムを追加
ALTER TABLE camps
    ADD COLUMN bus_fee_round_trip INT DEFAULT NULL AFTER first_day_lunch_included,
    ADD COLUMN bus_fee_separate TINYINT(1) NOT NULL DEFAULT 0 AFTER bus_fee_round_trip,
    ADD COLUMN use_rental_car TINYINT(1) NOT NULL DEFAULT 0 AFTER highway_fee_return,
    ADD COLUMN rental_car_fee INT DEFAULT NULL AFTER use_rental_car,
    ADD COLUMN rental_car_highway_fee INT DEFAULT NULL AFTER rental_car_fee,
    ADD COLUMN rental_car_capacity INT DEFAULT NULL AFTER rental_car_highway_fee;

-- 既存データの移行（往路・復路の合計を往復料金に設定し、別々設定フラグをON）
UPDATE camps
SET bus_fee_round_trip = COALESCE(bus_fee_outbound, 0) + COALESCE(bus_fee_return, 0),
    bus_fee_separate = 1
WHERE bus_fee_outbound IS NOT NULL OR bus_fee_return IS NOT NULL;

-- participantsテーブルにレンタカー利用カラムを追加
ALTER TABLE participants
    ADD COLUMN use_rental_car TINYINT(1) NOT NULL DEFAULT 0 AFTER use_return_bus;

-- participantsテーブルに学年・性別カラムを追加
ALTER TABLE participants
    ADD COLUMN grade TINYINT DEFAULT NULL AFTER name,
    ADD COLUMN gender ENUM('male', 'female') DEFAULT NULL AFTER grade;

SELECT 'マイグレーション完了: バス往復料金、レンタカーオプション、学年・性別を追加しました' AS message;
