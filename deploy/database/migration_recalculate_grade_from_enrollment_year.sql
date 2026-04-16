-- enrollment_year と academic_year から grade を再計算する
-- 背景: 10月引退処理でDBのgradeをOB/OGに書き換えていたが、
--       入学年度ベースの動的計算方式に移行したため、gradeを正しい値に戻す
--
-- ロジック:
--   在籍年数 = academic_year - enrollment_year + 1
--   1年目 → grade='1'
--   2年目 → grade='2'
--   3年目 → grade='3'  ※10月以降かどうかはアプリ側で動的判定
--   4年目以上 → grade='OB'/'OG'（性別で判定）
--
-- 対象: enrollment_year と academic_year の両方が設定されているレコード

-- 事前確認: 更新対象レコードの確認（UPDATEの前に確認したい場合はここだけ実行）
SELECT id, name_kanji, gender, enrollment_year, academic_year, grade,
    (academic_year - enrollment_year + 1) AS years_enrolled,
    CASE
        WHEN (academic_year - enrollment_year + 1) >= 4 THEN
            CASE gender WHEN 'male' THEN 'OB' ELSE 'OG' END
        WHEN (academic_year - enrollment_year + 1) = 3 THEN '3'
        WHEN (academic_year - enrollment_year + 1) = 2 THEN '2'
        WHEN (academic_year - enrollment_year + 1) = 1 THEN '1'
        ELSE grade
    END AS new_grade
FROM members
WHERE enrollment_year IS NOT NULL
  AND academic_year IS NOT NULL
  AND grade != CASE
        WHEN (academic_year - enrollment_year + 1) >= 4 THEN
            CASE gender WHEN 'male' THEN 'OB' ELSE 'OG' END
        WHEN (academic_year - enrollment_year + 1) = 3 THEN '3'
        WHEN (academic_year - enrollment_year + 1) = 2 THEN '2'
        WHEN (academic_year - enrollment_year + 1) = 1 THEN '1'
        ELSE grade
    END;

UPDATE members
SET grade = CASE
    WHEN (academic_year - enrollment_year + 1) >= 4 THEN
        CASE gender WHEN 'male' THEN 'OB' ELSE 'OG' END
    WHEN (academic_year - enrollment_year + 1) = 3 THEN '3'
    WHEN (academic_year - enrollment_year + 1) = 2 THEN '2'
    WHEN (academic_year - enrollment_year + 1) = 1 THEN '1'
    ELSE grade  -- 計算結果がおかしい場合は変更しない
END
WHERE enrollment_year IS NOT NULL
  AND academic_year IS NOT NULL;

SELECT
    grade,
    COUNT(*) AS count
FROM members
WHERE enrollment_year IS NOT NULL
  AND academic_year IS NOT NULL
GROUP BY grade
ORDER BY grade;

SELECT 'マイグレーション完了: enrollment_year から grade を再計算しました' AS message;
