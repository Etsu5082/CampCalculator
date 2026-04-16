-- 既存の meal_adjustments テーブルをクリアするマイグレーション
--
-- 背景:
-- 旧実装では Participant::generateMealAdjustments() が参加者作成・更新時に
-- meal_adjustments テーブルに自動的にレコードを追加していました。
--
-- 新実装では CalculationService::calculateAutoMealAdjustment() が
-- 宿泊ベースで正しく食事調整を計算するため、meal_adjustments テーブルの
-- 自動生成レコードは不要（むしろ二重計算の原因）になりました。
--
-- このマイグレーションは既存の自動生成された調整レコードをクリアします。
-- ※ユーザーが手動で追加した食事調整は、今後も meal_adjustments テーブルを
--   使用して管理できます。

-- 実行日: 2026-02-27

-- 全ての meal_adjustments レコードを削除
-- （今後は CalculationService の自動計算のみを使用）
TRUNCATE TABLE meal_adjustments;

-- 確認用クエリ（削除後に実行して空であることを確認）
-- SELECT COUNT(*) FROM meal_adjustments;
