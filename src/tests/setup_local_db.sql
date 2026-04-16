-- ローカルテスト用DBセットアップ
-- 実行: /Applications/MAMP/Library/bin/mysql -u root -proot < tests/setup_local_db.sql

CREATE DATABASE IF NOT EXISTS lesse_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lesse_test;

-- academic_years テーブル
CREATE TABLE IF NOT EXISTS academic_years (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    year            INT NOT NULL UNIQUE,
    start_date      DATE NOT NULL,
    end_date        DATE NOT NULL,
    is_current      TINYINT(1) NOT NULL DEFAULT 0,
    enrollment_open TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- members テーブル
CREATE TABLE IF NOT EXISTS members (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    name_kanji            VARCHAR(100) NOT NULL,
    name_kana             VARCHAR(100) NOT NULL,
    gender                ENUM('male','female') NOT NULL,
    grade                 VARCHAR(10) NOT NULL,
    faculty               VARCHAR(100) NOT NULL,
    department            VARCHAR(100),
    student_id            VARCHAR(50),
    phone                 VARCHAR(20),
    address               TEXT,
    emergency_contact     VARCHAR(20),
    birthdate             DATE,
    allergy               TEXT,
    line_name             VARCHAR(100),
    sns_allowed           TINYINT(1) DEFAULT 1,
    sports_registration_no VARCHAR(50),
    email                 VARCHAR(255),
    status                ENUM('pending','active','ob_og','withdrawn') NOT NULL DEFAULT 'pending',
    department_not_set    TINYINT(1) DEFAULT 0,
    enrollment_year       INT,
    academic_year         INT,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- camps テーブル
CREATE TABLE IF NOT EXISTS camps (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    name                     VARCHAR(255) NOT NULL,
    start_date               DATE NOT NULL,
    end_date                 DATE NOT NULL,
    nights                   INT NOT NULL DEFAULT 3,
    lodging_fee_per_night    INT DEFAULT 0,
    breakfast_add_price      INT DEFAULT 0,
    breakfast_remove_price   INT DEFAULT 0,
    lunch_add_price          INT DEFAULT 0,
    lunch_remove_price       INT DEFAULT 0,
    dinner_add_price         INT DEFAULT 0,
    dinner_remove_price      INT DEFAULT 0,
    insurance_fee            INT DEFAULT 0,
    court_fee_per_unit       INT DEFAULT 0,
    gym_fee_per_unit         INT DEFAULT 0,
    banquet_fee_per_person   INT DEFAULT 0,
    first_day_lunch_included TINYINT(1) DEFAULT 0,
    bus_fee_round_trip       INT DEFAULT 0,
    bus_fee_separate         TINYINT(1) DEFAULT 0,
    bus_fee_outbound         INT,
    bus_fee_return           INT,
    highway_fee_outbound     INT DEFAULT 0,
    highway_fee_return       INT DEFAULT 0,
    use_rental_car           TINYINT(1) DEFAULT 0,
    rental_car_fee           INT DEFAULT 0,
    rental_car_highway_fee   INT DEFAULT 0,
    rental_car_capacity      INT DEFAULT 5,
    hot_spring_tax           INT DEFAULT 0,
    created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- camp_tokens テーブル
CREATE TABLE IF NOT EXISTS camp_tokens (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    camp_id    INT NOT NULL,
    token      VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- participants テーブル
CREATE TABLE IF NOT EXISTS participants (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    camp_id          INT NOT NULL,
    name             VARCHAR(100) NOT NULL,
    grade            INT,
    gender           ENUM('male','female'),
    join_day         INT NOT NULL DEFAULT 1,
    join_timing      VARCHAR(50) NOT NULL DEFAULT 'morning',
    leave_day        INT NOT NULL,
    leave_timing     VARCHAR(50) NOT NULL DEFAULT 'return_bus',
    use_outbound_bus TINYINT(1) DEFAULT 1,
    use_return_bus   TINYINT(1) DEFAULT 1,
    use_rental_car   TINYINT(1) DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- camp_applications テーブル
CREATE TABLE IF NOT EXISTS camp_applications (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    camp_id          INT NOT NULL,
    member_id        INT NOT NULL,
    participant_id   INT,
    join_day         INT NOT NULL DEFAULT 1,
    join_timing      VARCHAR(50) NOT NULL DEFAULT 'outbound_bus',
    leave_day        INT NOT NULL DEFAULT 4,
    leave_timing     VARCHAR(50) NOT NULL DEFAULT 'return_bus',
    use_outbound_bus TINYINT(1) DEFAULT 1,
    use_return_bus   TINYINT(1) DEFAULT 1,
    status           ENUM('submitted','cancelled') NOT NULL DEFAULT 'submitted',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id)        REFERENCES camps(id)        ON DELETE CASCADE,
    FOREIGN KEY (member_id)      REFERENCES members(id)      ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- time_slots テーブル
CREATE TABLE IF NOT EXISTS time_slots (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    camp_id       INT NOT NULL,
    day_number    INT NOT NULL DEFAULT 1,
    slot_type     VARCHAR(50) NOT NULL DEFAULT 'tennis',
    activity_type VARCHAR(50) NOT NULL DEFAULT 'tennis',
    facility_fee  INT DEFAULT 0,
    court_count   INT DEFAULT 1,
    description   VARCHAR(255),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- expenses テーブル
CREATE TABLE IF NOT EXISTS expenses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    camp_id     INT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    amount      INT NOT NULL,
    target_type VARCHAR(50) DEFAULT 'all',
    target_day  INT,
    target_slot VARCHAR(50),
    payer_id    INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- settings テーブル（認証用）
CREATE TABLE IF NOT EXISTS settings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Setup complete!' AS result;
