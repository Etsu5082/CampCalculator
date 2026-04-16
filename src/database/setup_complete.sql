-- ============================================================
-- 合宿費用計算アプリ 完全セットアップSQL
-- 新サーバーへの移行時にこのファイル1つだけ実行してください
-- ============================================================

-- ============================================================
-- テーブル作成
-- ============================================================

-- 合宿テーブル
CREATE TABLE IF NOT EXISTS camps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    nights INT NOT NULL,
    lodging_fee_per_night INT NOT NULL DEFAULT 0,
    hot_spring_tax INT NOT NULL DEFAULT 150,
    breakfast_add_price INT NOT NULL DEFAULT 0,
    breakfast_remove_price INT NOT NULL DEFAULT 0,
    lunch_add_price INT NOT NULL DEFAULT 0,
    lunch_remove_price INT NOT NULL DEFAULT 0,
    dinner_add_price INT NOT NULL DEFAULT 0,
    dinner_remove_price INT NOT NULL DEFAULT 0,
    insurance_fee INT NOT NULL DEFAULT 0,
    -- コート・体育館単価
    court_fee_per_unit INT DEFAULT NULL COMMENT 'テニスコート1面あたり料金',
    gym_fee_per_unit INT DEFAULT NULL COMMENT '体育館1コマあたり料金',
    -- 宴会場単価
    banquet_fee_per_person INT DEFAULT NULL COMMENT '宴会場1人あたり料金',
    first_day_lunch_included TINYINT(1) NOT NULL DEFAULT 0,
    -- バス料金（往復一括または別々）
    bus_fee_round_trip INT DEFAULT NULL,
    bus_fee_separate TINYINT(1) NOT NULL DEFAULT 0,
    bus_fee_outbound INT DEFAULT NULL,
    bus_fee_return INT DEFAULT NULL,
    -- バス高速代
    highway_fee_outbound INT DEFAULT NULL,
    highway_fee_return INT DEFAULT NULL,
    -- レンタカーオプション
    use_rental_car TINYINT(1) NOT NULL DEFAULT 0,
    rental_car_fee INT DEFAULT NULL,
    rental_car_highway_fee INT DEFAULT NULL,
    rental_car_capacity INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- タイムスロットテーブル
CREATE TABLE IF NOT EXISTS time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    day_number INT NOT NULL,
    slot_type ENUM('outbound', 'morning', 'afternoon', 'banquet', 'return') NOT NULL,
    activity_type ENUM('tennis', 'gym', 'banquet', 'bus') DEFAULT NULL,
    facility_fee INT DEFAULT NULL,
    court_count INT DEFAULT 1 COMMENT '使用コート面数',
    description VARCHAR(200) DEFAULT NULL,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (camp_id, day_number, slot_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 参加者テーブル
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    grade TINYINT DEFAULT NULL,
    gender ENUM('male', 'female') DEFAULT NULL,
    join_day INT NOT NULL DEFAULT 1,
    join_timing VARCHAR(20) NOT NULL DEFAULT 'outbound_bus',
    leave_day INT NOT NULL,
    leave_timing VARCHAR(20) NOT NULL DEFAULT 'return_bus',
    use_outbound_bus TINYINT(1) NOT NULL DEFAULT 1,
    use_return_bus TINYINT(1) NOT NULL DEFAULT 1,
    use_rental_car TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 参加者×スロット中間テーブル
CREATE TABLE IF NOT EXISTS participant_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    time_slot_id INT NOT NULL,
    is_attending TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant_slot (participant_id, time_slot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 食事調整テーブル
CREATE TABLE IF NOT EXISTS meal_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    day_number INT NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'dinner') NOT NULL,
    adjustment_type ENUM('add', 'remove') NOT NULL,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meal_adj (participant_id, day_number, meal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 雑費テーブル
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    amount INT NOT NULL,
    target_type ENUM('all', 'slot') NOT NULL DEFAULT 'all',
    target_day INT DEFAULT NULL,
    target_slot ENUM('morning', 'afternoon', 'banquet', 'night') DEFAULT NULL,
    payer_id INT DEFAULT NULL COMMENT '建て替え者（参加者ID）',
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE,
    CONSTRAINT fk_expenses_payer FOREIGN KEY (payer_id) REFERENCES participants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 設定テーブル
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 年度管理テーブル
CREATE TABLE IF NOT EXISTS academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL UNIQUE COMMENT '年度（例: 2025, 2026）',
    start_date DATE NOT NULL COMMENT '年度開始日',
    end_date DATE NOT NULL COMMENT '年度終了日',
    is_current TINYINT(1) NOT NULL DEFAULT 0 COMMENT '現在年度フラグ',
    enrollment_open TINYINT(1) NOT NULL DEFAULT 0 COMMENT '入会受付中フラグ',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='年度管理';

-- 会員名簿テーブル
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_kanji VARCHAR(100) NOT NULL COMMENT '名前（漢字）',
    name_kana VARCHAR(100) NOT NULL COMMENT '名前（カタカナ）',
    gender ENUM('male', 'female') NOT NULL COMMENT '性別',
    grade VARCHAR(10) NOT NULL COMMENT '学年（1,2,3,4,M1,M2,OB,OG）',
    faculty VARCHAR(50) NOT NULL COMMENT '学部',
    department VARCHAR(100) NOT NULL COMMENT '学科/学系',
    student_id VARCHAR(20) NOT NULL COMMENT '学籍番号',
    phone VARCHAR(20) NOT NULL COMMENT '電話番号',
    address TEXT NOT NULL COMMENT '住所',
    emergency_contact VARCHAR(20) NOT NULL COMMENT '緊急連絡先',
    birthdate DATE NOT NULL COMMENT '生年月日',
    allergy TEXT DEFAULT NULL COMMENT 'アレルギー情報',
    line_name VARCHAR(100) NOT NULL COMMENT 'LINE名',
    sns_allowed TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'SNS写真投稿可否',
    sports_registration_no VARCHAR(100) DEFAULT NULL COMMENT '都営スポーツレクリエーション登録番号',
    email VARCHAR(255) DEFAULT NULL COMMENT 'メールアドレス',
    status ENUM('pending', 'active', 'ob_og', 'withdrawn') NOT NULL DEFAULT 'pending',
    department_not_set TINYINT(1) NOT NULL DEFAULT 0 COMMENT '学科未設定フラグ',
    enrollment_year INT DEFAULT NULL COMMENT '入学年度',
    academic_year INT NOT NULL DEFAULT 2025 COMMENT '所属年度',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_id_year (student_id, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会員名簿';

-- 合宿申し込みURLトークンテーブル
CREATE TABLE IF NOT EXISTS camp_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    deadline DATETIME DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合宿申し込みURLトークン';

-- 合宿申し込みテーブル
CREATE TABLE IF NOT EXISTS camp_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    member_id INT NOT NULL,
    participant_id INT DEFAULT NULL,
    join_day INT NOT NULL DEFAULT 1,
    join_timing VARCHAR(20) NOT NULL DEFAULT 'outbound_bus',
    leave_day INT NOT NULL,
    leave_timing VARCHAR(20) NOT NULL DEFAULT 'return_bus',
    use_outbound_bus TINYINT(1) NOT NULL DEFAULT 1,
    use_return_bus TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('submitted', 'confirmed', 'change_requested', 'cancelled') NOT NULL DEFAULT 'submitted',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE SET NULL,
    UNIQUE KEY unique_camp_member (camp_id, member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合宿申し込み';

-- 変更リクエストテーブル
CREATE TABLE IF NOT EXISTS change_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    member_id INT NOT NULL,
    change_type ENUM('schedule', 'transport', 'cancel') NOT NULL,
    old_value TEXT DEFAULT NULL,
    new_value TEXT NOT NULL,
    reason TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_at DATETIME DEFAULT NULL,
    reviewed_by VARCHAR(50) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES camp_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='変更リクエスト';

-- メール送信ログテーブル
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT DEFAULT NULL,
    email_type VARCHAR(50) NOT NULL,
    to_address VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    sent_at DATETIME DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='メール送信ログ';

-- ============================================================
-- インデックス
-- ============================================================
CREATE INDEX idx_time_slots_camp ON time_slots(camp_id);
CREATE INDEX idx_participants_camp ON participants(camp_id);
CREATE INDEX idx_participant_slots_participant ON participant_slots(participant_id);
CREATE INDEX idx_expenses_camp ON expenses(camp_id);
CREATE INDEX idx_members_name_kanji ON members(name_kanji);
CREATE INDEX idx_members_name_kana ON members(name_kana);
CREATE INDEX idx_members_status ON members(status);
CREATE INDEX idx_members_grade ON members(grade);
CREATE INDEX idx_members_faculty ON members(faculty);
CREATE INDEX idx_members_enrollment_year ON members(enrollment_year);
CREATE INDEX idx_members_academic_year ON members(academic_year);
CREATE INDEX idx_members_status_year ON members(status, academic_year);
CREATE INDEX idx_camp_tokens_token ON camp_tokens(token);
CREATE INDEX idx_camp_applications_camp ON camp_applications(camp_id);
CREATE INDEX idx_camp_applications_member ON camp_applications(member_id);
CREATE INDEX idx_change_requests_application ON change_requests(application_id);
CREATE INDEX idx_change_requests_status ON change_requests(status);
CREATE INDEX idx_email_logs_member ON email_logs(member_id);
CREATE INDEX idx_email_logs_status ON email_logs(status);

-- ============================================================
-- 初期データ
-- ============================================================

-- 初期パスワード設定（デフォルト: password）
-- 本番環境では必ず変更してください
INSERT INTO settings (setting_key, setting_value) VALUES
('password', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 現在年度データ
INSERT INTO academic_years (year, start_date, end_date, is_current, enrollment_open)
VALUES (2026, '2026-04-01', '2027-03-31', 1, 1)
ON DUPLICATE KEY UPDATE is_current = VALUES(is_current);

-- ============================================================
SELECT 'セットアップ完了！デフォルトパスワードは "password" です。必ず変更してください。' AS message;
-- ============================================================
