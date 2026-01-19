-- 合宿費用計算アプリ データベースセットアップ
-- このSQLをMySQL/phpMyAdminで実行してください

-- データベース作成（必要に応じて）
-- CREATE DATABASE IF NOT EXISTS camp_calc DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE camp_calc;

-- 合宿テーブル
CREATE TABLE IF NOT EXISTS camps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    nights INT NOT NULL,
    lodging_fee_per_night INT NOT NULL DEFAULT 0,
    breakfast_add_price INT NOT NULL DEFAULT 0,
    breakfast_remove_price INT NOT NULL DEFAULT 0,
    lunch_add_price INT NOT NULL DEFAULT 0,
    lunch_remove_price INT NOT NULL DEFAULT 0,
    dinner_add_price INT NOT NULL DEFAULT 0,
    dinner_remove_price INT NOT NULL DEFAULT 0,
    insurance_fee INT NOT NULL DEFAULT 0,
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
    join_timing ENUM('morning', 'lunch', 'afternoon', 'dinner', 'night') NOT NULL DEFAULT 'morning',
    leave_day INT NOT NULL,
    leave_timing ENUM('morning', 'after_breakfast', 'lunch', 'after_lunch', 'afternoon', 'dinner', 'night') NOT NULL DEFAULT 'after_lunch',
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
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 設定テーブル
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- インデックス
CREATE INDEX idx_time_slots_camp ON time_slots(camp_id);
CREATE INDEX idx_participants_camp ON participants(camp_id);
CREATE INDEX idx_participant_slots_participant ON participant_slots(participant_id);
CREATE INDEX idx_expenses_camp ON expenses(camp_id);

-- 初期パスワード設定（デフォルト: password）
-- 本番環境では必ず変更してください
INSERT INTO settings (setting_key, setting_value) VALUES
('password', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 完了メッセージ
SELECT 'データベースのセットアップが完了しました。デフォルトパスワードは "password" です。' AS message;
