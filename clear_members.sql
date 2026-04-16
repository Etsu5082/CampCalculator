-- 会員データを全削除
-- PHPMyAdminで実行してください

-- 外部キー制約があるため、関連テーブルも確認
DELETE FROM change_requests;
DELETE FROM camp_applications;
DELETE FROM email_logs WHERE member_id IS NOT NULL;
DELETE FROM members;

-- AUTO_INCREMENTをリセット（IDを1から再開）
ALTER TABLE members AUTO_INCREMENT = 1;
ALTER TABLE camp_applications AUTO_INCREMENT = 1;
ALTER TABLE change_requests AUTO_INCREMENT = 1;

-- 削除確認
SELECT 'members' as table_name, COUNT(*) as count FROM members
UNION ALL
SELECT 'camp_applications', COUNT(*) FROM camp_applications
UNION ALL
SELECT 'change_requests', COUNT(*) FROM change_requests;
