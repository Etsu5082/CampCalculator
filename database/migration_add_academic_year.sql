-- 年度管理機能の追加
-- 実行順序: 1. academic_years テーブル作成 → 2. members テーブル拡張

-- =====================================================
-- 1. academic_years（年度管理）テーブル作成
-- =====================================================
CREATE TABLE IF NOT EXISTS academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL UNIQUE COMMENT '年度（例: 2025, 2026）',
    start_date DATE NOT NULL COMMENT '年度開始日',
    end_date DATE NOT NULL COMMENT '年度終了日',
    is_current TINYINT(1) NOT NULL DEFAULT 0 COMMENT '現在年度フラグ',
    enrollment_open TINYINT(1) NOT NULL DEFAULT 0 COMMENT '入会受付中フラグ',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='年度管理';

-- =====================================================
-- 2. members テーブルに academic_year カラムを追加
-- =====================================================
ALTER TABLE members
ADD COLUMN academic_year INT NOT NULL DEFAULT 2025 COMMENT '所属年度' AFTER enrollment_year;

-- =====================================================
-- 3. student_id の UNIQUE 制約を変更
--    単一UNIQUE → 年度ごとのUNIQUE（同一人物が複数年度に存在可能）
-- =====================================================
ALTER TABLE members
DROP INDEX student_id,
ADD UNIQUE KEY unique_student_id_year (student_id, academic_year);

-- =====================================================
-- 4. インデックス追加
-- =====================================================
CREATE INDEX idx_members_academic_year ON members(academic_year);
CREATE INDEX idx_members_status_year ON members(status, academic_year);

-- =====================================================
-- 5. 現在年度のデータ作成
-- =====================================================
INSERT INTO academic_years (year, start_date, end_date, is_current, enrollment_open)
VALUES
    (2025, '2025-04-01', '2026-03-31', 1, 1);

-- =====================================================
-- 6. 既存データに年度を設定
-- =====================================================
UPDATE members SET academic_year = 2025 WHERE academic_year = 2025;

-- 確認用SQL
SELECT
    '年度テーブル' as category,
    COUNT(*) as count
FROM academic_years
UNION ALL
SELECT
    '会員（2025年度）',
    COUNT(*)
FROM members
WHERE academic_year = 2025;
