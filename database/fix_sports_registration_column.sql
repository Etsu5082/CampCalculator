-- sports_registration_no カラムの長さを拡張
-- VARCHAR(20) → VARCHAR(100) に変更

ALTER TABLE members
MODIFY COLUMN sports_registration_no VARCHAR(100) DEFAULT NULL COMMENT '都営スポーツレクリエーション登録番号';
