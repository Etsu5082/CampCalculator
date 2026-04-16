-- 会員名簿・申し込み機能用テーブル作成
-- 実行順序: 1. members → 2. camp_tokens → 3. camp_applications → 4. change_requests → 5. email_logs

-- =====================================================
-- 2.8 members（会員名簿）
-- =====================================================
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_kanji VARCHAR(100) NOT NULL COMMENT '名前（漢字、全角スペース区切り）',
    name_kana VARCHAR(100) NOT NULL COMMENT '名前（カタカナ、全角スペース区切り）',
    gender ENUM('male', 'female') NOT NULL COMMENT '性別',
    grade VARCHAR(10) NOT NULL COMMENT '学年（1,2,3,4,M1,M2,OB,OG）',
    faculty VARCHAR(50) NOT NULL COMMENT '学部',
    department VARCHAR(100) NOT NULL COMMENT '学科/学系',
    student_id VARCHAR(20) NOT NULL UNIQUE COMMENT '学籍番号（CD付き）',
    phone VARCHAR(20) NOT NULL COMMENT '電話番号（ハイフンあり半角）',
    address TEXT NOT NULL COMMENT '住所',
    emergency_contact VARCHAR(20) NOT NULL COMMENT '緊急連絡先（ハイフンあり半角）',
    birthdate DATE NOT NULL COMMENT '生年月日',
    allergy TEXT DEFAULT NULL COMMENT 'アレルギー情報',
    line_name VARCHAR(100) NOT NULL COMMENT '現在のLINE名',
    sns_allowed TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'SNS写真投稿可否',
    sports_registration_no VARCHAR(100) DEFAULT NULL COMMENT '都営スポーツレクリエーション登録番号',
    email VARCHAR(255) DEFAULT NULL COMMENT 'メールアドレス（通知用）',
    status ENUM('pending', 'active', 'ob_og', 'withdrawn') NOT NULL DEFAULT 'pending' COMMENT 'ステータス',
    department_not_set TINYINT(1) NOT NULL DEFAULT 0 COMMENT '学科未設定フラグ（基幹2年の進振り後用）',
    enrollment_year INT DEFAULT NULL COMMENT '入学年度（学籍番号から自動判定）',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会員名簿';

-- =====================================================
-- 2.9 camp_tokens（合宿申し込みURLトークン）
-- =====================================================
CREATE TABLE IF NOT EXISTS camp_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL COMMENT '外部キー → camps.id',
    token VARCHAR(64) NOT NULL UNIQUE COMMENT 'URLトークン（ランダム生成）',
    deadline DATETIME DEFAULT NULL COMMENT '申し込み締切日時',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合宿申し込みURLトークン';

-- =====================================================
-- 2.10 camp_applications（合宿申し込み）
-- =====================================================
CREATE TABLE IF NOT EXISTS camp_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL COMMENT '外部キー → camps.id',
    member_id INT NOT NULL COMMENT '外部キー → members.id',
    participant_id INT DEFAULT NULL COMMENT '外部キー → participants.id（作成後に紐付け）',
    join_day INT NOT NULL DEFAULT 1 COMMENT '参加開始日',
    join_timing ENUM('outbound_bus', 'morning', 'lunch', 'afternoon', 'dinner', 'night') NOT NULL DEFAULT 'outbound_bus' COMMENT '参加開始タイミング',
    leave_day INT NOT NULL COMMENT '離脱日',
    leave_timing ENUM('morning', 'after_breakfast', 'lunch', 'after_lunch', 'afternoon', 'dinner', 'night', 'return_bus') NOT NULL DEFAULT 'return_bus' COMMENT '離脱タイミング',
    use_outbound_bus TINYINT(1) NOT NULL DEFAULT 1 COMMENT '往路バス利用',
    use_return_bus TINYINT(1) NOT NULL DEFAULT 1 COMMENT '復路バス利用',
    status ENUM('submitted', 'confirmed', 'change_requested', 'cancelled') NOT NULL DEFAULT 'submitted' COMMENT '申し込みステータス',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE SET NULL,
    UNIQUE KEY unique_camp_member (camp_id, member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合宿申し込み';

-- =====================================================
-- 2.11 change_requests（変更リクエスト）
-- =====================================================
CREATE TABLE IF NOT EXISTS change_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL COMMENT '外部キー → camp_applications.id',
    member_id INT NOT NULL COMMENT '外部キー → members.id（リクエスト者）',
    change_type ENUM('schedule', 'transport', 'cancel') NOT NULL COMMENT '変更種別',
    old_value TEXT DEFAULT NULL COMMENT '変更前の値（JSON形式）',
    new_value TEXT NOT NULL COMMENT '変更後の値（JSON形式）',
    reason TEXT DEFAULT NULL COMMENT '変更理由',
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' COMMENT 'リクエストステータス',
    reviewed_at DATETIME DEFAULT NULL COMMENT '審査日時',
    reviewed_by VARCHAR(50) DEFAULT NULL COMMENT '審査者',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES camp_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='変更リクエスト';

-- =====================================================
-- 2.12 email_logs（メール送信ログ）
-- =====================================================
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT DEFAULT NULL COMMENT '外部キー → members.id',
    email_type VARCHAR(50) NOT NULL COMMENT 'メール種別',
    to_address VARCHAR(255) NOT NULL COMMENT '送信先アドレス',
    subject VARCHAR(255) NOT NULL COMMENT '件名',
    body TEXT NOT NULL COMMENT '本文',
    status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending' COMMENT '送信ステータス',
    sent_at DATETIME DEFAULT NULL COMMENT '送信日時',
    error_message TEXT DEFAULT NULL COMMENT 'エラーメッセージ',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='メール送信ログ';

-- =====================================================
-- インデックス作成
-- =====================================================
CREATE INDEX idx_members_name_kanji ON members(name_kanji);
CREATE INDEX idx_members_name_kana ON members(name_kana);
CREATE INDEX idx_members_status ON members(status);
CREATE INDEX idx_members_grade ON members(grade);
CREATE INDEX idx_members_faculty ON members(faculty);
CREATE INDEX idx_members_enrollment_year ON members(enrollment_year);
CREATE INDEX idx_camp_tokens_token ON camp_tokens(token);
CREATE INDEX idx_camp_applications_camp ON camp_applications(camp_id);
CREATE INDEX idx_camp_applications_member ON camp_applications(member_id);
CREATE INDEX idx_change_requests_application ON change_requests(application_id);
CREATE INDEX idx_change_requests_status ON change_requests(status);
CREATE INDEX idx_email_logs_member ON email_logs(member_id);
CREATE INDEX idx_email_logs_status ON email_logs(status);
