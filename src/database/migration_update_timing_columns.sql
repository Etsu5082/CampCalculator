-- 参加タイミングのカラム型を変更するマイグレーション
--
-- 参加開始タイミング値 (join_timing):
--   outbound_bus  : 往路バスから（1日目のみ）
--   breakfast     : 朝食から
--   morning       : 午前イベントから（朝食を食べずに参加）
--   lunch         : 昼食から
--   afternoon     : 午後イベントから（昼食を食べずに参加）
--   dinner        : 夕食から
--   night         : 夜から（夕食を食べずに参加）
--   lodging       : 宿泊から
--
-- 参加終了タイミング値 (leave_timing):
--   before_breakfast : 朝食前まで（朝食を食べずに帰る）
--   breakfast        : 朝食まで（朝食を食べて午前イベントに参加せず帰る）
--   morning          : 午前イベントまで（午前イベントに参加して昼食を食べずに帰る）
--   lunch            : 昼食まで（昼食を食べて午後イベントに参加せず帰る）
--   afternoon        : 午後イベントまで（午後イベントに参加して夕食を食べずに帰る）
--   dinner           : 夕食まで（夕食を食べて夜イベントに参加せず帰る）※最終日以外
--   night            : 夜まで（夜イベントに参加して宿泊する）※最終日以外
--   return_bus       : 復路バスまで（最終日のみ）
--
-- ※「宿泊まで」は翌日の「朝食前まで」と同義のため選択肢から削除

-- join_timing カラムを VARCHAR に変更（新しいタイミング値に対応）
ALTER TABLE participants
MODIFY COLUMN join_timing VARCHAR(20) NOT NULL DEFAULT 'outbound_bus';

-- leave_timing カラムを VARCHAR に変更（新しいタイミング値に対応）
ALTER TABLE participants
MODIFY COLUMN leave_timing VARCHAR(20) NOT NULL DEFAULT 'return_bus';

-- 完了メッセージ
SELECT 'タイミングカラムの更新が完了しました' AS message;
