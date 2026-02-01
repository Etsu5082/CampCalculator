-- 入湯税フィールド追加マイグレーション
-- 実行日: 2026-02-01
-- 説明: campsテーブルに入湯税（1泊あたり）を追加

ALTER TABLE camps
ADD COLUMN hot_spring_tax INT NOT NULL DEFAULT 150
AFTER insurance_fee;

-- 既存データの確認用クエリ（実行後に確認）
-- SELECT id, name, hot_spring_tax FROM camps;
