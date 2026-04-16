<?php
/**
 * 会員名簿モデル
 */

if (!class_exists('Member')) {

class Member
{
    private Database $db;

    /**
     * ステータス定数
     */
    const STATUS_PENDING = 'pending';      // 承認待ち
    const STATUS_ACTIVE = 'active';        // 現役
    const STATUS_OB_OG = 'ob_og';          // OB/OG
    const STATUS_WITHDRAWN = 'withdrawn';  // 退会

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 全件取得
     */
    public function all(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM members ORDER BY name_kana ASC"
        );
    }

    /**
     * ID指定で取得
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM members WHERE id = ?",
            [$id]
        );
    }

    /**
     * 検索・フィルタ・ページネーション対応の一覧取得
     *
     * @param array $filters フィルタ条件
     * @param int $perPage 1ページあたりの件数
     * @param int $offset オフセット
     * @return array 会員リスト
     */
    public function search(array $filters = [], int $perPage = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        // 検索条件（名前・カナ）
        if (!empty($filters['search'])) {
            $where[] = "(name_kanji LIKE ? OR name_kana LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        // フィルタ: 学年
        if (isset($filters['grade']) && $filters['grade'] !== '') {
            $where[] = "grade = ?";
            $params[] = $filters['grade'];
        }

        // フィルタ: 学部
        if (isset($filters['faculty']) && $filters['faculty'] !== '') {
            $where[] = "faculty = ?";
            $params[] = $filters['faculty'];
        }

        // フィルタ: ステータス
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        // フィルタ: 学科未設定
        if (isset($filters['department_not_set']) && $filters['department_not_set'] !== '') {
            $where[] = "department_not_set = ?";
            $params[] = (int)$filters['department_not_set'];
        }

        // フィルタ: 入学年度
        if (isset($filters['enrollment_year']) && $filters['enrollment_year'] !== '') {
            $where[] = "enrollment_year = ?";
            $params[] = $filters['enrollment_year'];
        }

        // フィルタ: 性別
        if (isset($filters['gender']) && $filters['gender'] !== '') {
            $where[] = "gender = ?";
            $params[] = $filters['gender'];
        }

        // フィルタ: 年度
        if (isset($filters['academic_year']) && $filters['academic_year'] !== '') {
            $where[] = "academic_year = ?";
            $params[] = (int)$filters['academic_year'];
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        // データ取得
        $sql = "SELECT * FROM members {$whereClause} ORDER BY name_kana ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * 検索条件に一致する件数を取得
     *
     * @param array $filters フィルタ条件
     * @return int 件数
     */
    public function countSearch(array $filters = []): int
    {
        $where = [];
        $params = [];

        // 検索条件（名前・カナ）
        if (!empty($filters['search'])) {
            $where[] = "(name_kanji LIKE ? OR name_kana LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        // フィルタ: 学年
        if (isset($filters['grade']) && $filters['grade'] !== '') {
            $where[] = "grade = ?";
            $params[] = $filters['grade'];
        }

        // フィルタ: 学部
        if (isset($filters['faculty']) && $filters['faculty'] !== '') {
            $where[] = "faculty = ?";
            $params[] = $filters['faculty'];
        }

        // フィルタ: ステータス
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        // フィルタ: 学科未設定
        if (isset($filters['department_not_set']) && $filters['department_not_set'] !== '') {
            $where[] = "department_not_set = ?";
            $params[] = (int)$filters['department_not_set'];
        }

        // フィルタ: 入学年度
        if (isset($filters['enrollment_year']) && $filters['enrollment_year'] !== '') {
            $where[] = "enrollment_year = ?";
            $params[] = $filters['enrollment_year'];
        }

        // フィルタ: 性別
        if (isset($filters['gender']) && $filters['gender'] !== '') {
            $where[] = "gender = ?";
            $params[] = $filters['gender'];
        }

        // フィルタ: 年度
        if (isset($filters['academic_year']) && $filters['academic_year'] !== '') {
            $where[] = "academic_year = ?";
            $params[] = (int)$filters['academic_year'];
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT COUNT(*) as total FROM members {$whereClause}";
        $result = $this->db->fetch($sql, $params);

        return $result['total'] ?? 0;
    }

    /**
     * 新規作成
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO members (
            name_kanji, name_kana, gender, grade, faculty, department,
            student_id, phone, address, emergency_contact, birthdate,
            allergy, line_name, sns_allowed, sports_registration_no, email,
            status, department_not_set, enrollment_year, academic_year
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $data['name_kanji'],
            $data['name_kana'],
            $data['gender'],
            $data['grade'],
            $data['faculty'],
            $data['department'],
            $data['student_id'],
            $data['phone'],
            $data['address'],
            $data['emergency_contact'],
            $data['birthdate'],
            $data['allergy'] ?? null,
            $data['line_name'],
            $data['sns_allowed'] ?? 1,
            $data['sports_registration_no'] ?? null,
            $data['email'] ?? null,
            $data['status'] ?? self::STATUS_PENDING,
            $data['department_not_set'] ?? 0,
            $data['enrollment_year'] ?? null,
            $data['academic_year'] ?? null,
        ]);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = [
            'name_kanji', 'name_kana', 'gender', 'grade', 'faculty', 'department',
            'student_id', 'phone', 'address', 'emergency_contact', 'birthdate',
            'allergy', 'line_name', 'sns_allowed', 'sports_registration_no', 'email',
            'status', 'department_not_set', 'enrollment_year', 'academic_year',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE members SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->execute($sql, $values) > 0;
    }

    /**
     * 削除
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM members WHERE id = ?", [$id]) > 0;
    }

    /**
     * 学籍番号で検索
     *
     * @param string $studentId 学籍番号
     * @return array|null 会員情報
     */
    public function findByStudentId(string $studentId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM members WHERE student_id = ?",
            [$studentId]
        );
    }

    /**
     * 学籍番号の重複チェック
     *
     * @param string $studentId 学籍番号
     * @return bool 存在する場合true
     */
    public function existsByStudentId(string $studentId): bool
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM members WHERE student_id = ?",
            [$studentId]
        );
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * メールアドレスで検索
     *
     * @param string $email メールアドレス
     * @return array|null 会員情報
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM members WHERE email = ?",
            [$email]
        );
    }

    /**
     * 承認待ち会員を取得
     *
     * @return array 会員リスト
     */
    public function getPending(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM members WHERE status = ? ORDER BY created_at DESC",
            [self::STATUS_PENDING]
        );
    }

    /**
     * ステータス別に会員を取得
     *
     * @param string $status ステータス
     * @return array 会員リスト
     */
    public function getByStatus(string $status): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM members WHERE status = ? ORDER BY name_kana ASC",
            [$status]
        );
    }

    /**
     * ステータスを更新
     *
     * @param int $id 会員ID
     * @param string $status 新しいステータス
     * @param string $reason 理由（却下時など）
     * @return bool 成功/失敗
     */
    public function updateStatus(int $id, string $status, string $reason = ''): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * ステータス別の集計を取得
     *
     * @return array ['pending' => int, 'active' => int, 'ob_og' => int, 'withdrawn' => int, 'total' => int]
     */
    public function getStatusCounts(): array
    {
        $sql = "SELECT
                    status,
                    COUNT(*) as count
                FROM members
                GROUP BY status";

        $results = $this->db->fetchAll($sql);

        $counts = [
            self::STATUS_PENDING => 0,
            self::STATUS_ACTIVE => 0,
            self::STATUS_OB_OG => 0,
            self::STATUS_WITHDRAWN => 0,
            'total' => 0,
        ];

        foreach ($results as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = (int)$row['count'];
            }
            $counts['total'] += (int)$row['count'];
        }

        return $counts;
    }

    /**
     * 学年別の集計を取得
     *
     * @return array ['1' => int, '2' => int, '3' => int, '4' => int, ...]
     */
    public function getGradeCounts(): array
    {
        $sql = "SELECT
                    grade,
                    COUNT(*) as count
                FROM members
                WHERE status = ?
                GROUP BY grade";

        $results = $this->db->fetchAll($sql, [self::STATUS_ACTIVE]);

        $counts = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            'M1' => 0,
            'M2' => 0,
            'OB' => 0,
            'OG' => 0,
        ];

        foreach ($results as $row) {
            if (isset($counts[$row['grade']])) {
                $counts[$row['grade']] = (int)$row['count'];
            }
        }

        return $counts;
    }

    /**
     * 学科未設定の会員を取得
     *
     * @return array 会員リスト
     */
    public function getDepartmentNotSet(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM members WHERE department_not_set = 1 ORDER BY name_kana ASC"
        );
    }

    /**
     * 入学年度一覧を取得
     *
     * @return array 入学年度リスト
     */
    public function getEnrollmentYears(): array
    {
        $results = $this->db->fetchAll(
            "SELECT DISTINCT enrollment_year FROM members
             WHERE enrollment_year IS NOT NULL
             ORDER BY enrollment_year DESC"
        );

        return array_column($results, 'enrollment_year');
    }

    /**
     * 一括インポート
     *
     * @param array $members 会員データの配列
     * @return array ['imported' => int, 'updated' => int, 'errors' => array]
     */
    public function bulkImport(array $members): array
    {
        $imported = 0;
        $updated = 0;
        $errors = [];

        // トランザクションを使わず、個別にコミット（エラーがあっても続行）
        foreach ($members as $index => $member) {
            try {
                // 学籍番号で既存チェック
                $existing = $this->findByStudentId($member['student_id']);

                if ($existing) {
                    // 既存の場合は更新
                    $this->update($existing['id'], $member);
                    $updated++;
                } else {
                    // 新規の場合は作成
                    $this->create($member);
                    $imported++;
                }
            } catch (Exception $e) {
                // 個別のエラーは記録するが、処理は続行
                $name = $member['name_kanji'] ?? '不明';
                $errors[] = "{$name} (行" . ($index + 2) . "): " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * 学籍番号と年度で検索
     *
     * @param string $studentId 学籍番号
     * @param int $year 年度
     * @return array|null 会員情報
     */
    public function findByStudentIdAndYear(string $studentId, int $year): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM members WHERE student_id = ? AND academic_year = ?",
            [$studentId, $year]
        );
    }

    /**
     * 前年度から名前で検索
     *
     * @param string $name 名前（カナまたは漢字）
     * @param int $year 検索対象年度
     * @return array 会員リスト
     */
    public function searchPreviousYear(string $name, int $year): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM members
             WHERE academic_year = ?
             AND (name_kanji LIKE ? OR name_kana LIKE ?)
             ORDER BY name_kana ASC",
            [$year, "%{$name}%", "%{$name}%"]
        );
    }

    /**
     * 次年度にコピー（継続入会）
     *
     * @param int $memberId 元となる会員ID
     * @param int $newYear 新しい年度
     * @return int 新しく作成された会員ID
     */
    public function copyToNextYear(int $memberId, int $newYear): int
    {
        // 元のデータを取得
        $member = $this->find($memberId);
        if (!$member) {
            throw new Exception('会員が見つかりません');
        }

        // 学年を自動的に+1
        $newGrade = $this->calculateNextGrade($member['grade'], $member['gender']);

        // 新年度のデータを作成
        $newData = [
            'name_kanji' => $member['name_kanji'],
            'name_kana' => $member['name_kana'],
            'gender' => $member['gender'],
            'grade' => $newGrade,
            'faculty' => $member['faculty'],
            'department' => $member['department'],
            'student_id' => $member['student_id'],
            'phone' => $member['phone'],
            'address' => $member['address'],
            'emergency_contact' => $member['emergency_contact'],
            'birthdate' => $member['birthdate'],
            'allergy' => $member['allergy'],
            'line_name' => $member['line_name'],
            'sns_allowed' => $member['sns_allowed'],
            'sports_registration_no' => $member['sports_registration_no'],
            'email' => $member['email'],
            'status' => self::STATUS_ACTIVE,  // 継続入会なので即座にactive
            'department_not_set' => $member['department_not_set'],
            'enrollment_year' => $member['enrollment_year'],
            'academic_year' => $newYear,
        ];

        // 挿入
        $sql = "INSERT INTO members (
            name_kanji, name_kana, gender, grade, faculty, department,
            student_id, phone, address, emergency_contact, birthdate,
            allergy, line_name, sns_allowed, sports_registration_no, email,
            status, department_not_set, enrollment_year, academic_year
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->insert($sql, [
            $newData['name_kanji'],
            $newData['name_kana'],
            $newData['gender'],
            $newData['grade'],
            $newData['faculty'],
            $newData['department'],
            $newData['student_id'],
            $newData['phone'],
            $newData['address'],
            $newData['emergency_contact'],
            $newData['birthdate'],
            $newData['allergy'],
            $newData['line_name'],
            $newData['sns_allowed'],
            $newData['sports_registration_no'],
            $newData['email'],
            $newData['status'],
            $newData['department_not_set'],
            $newData['enrollment_year'],
            $newData['academic_year'],
        ]);
    }

    /**
     * 次の学年を計算
     *
     * @param string $currentGrade 現在の学年
     * @param string $gender 性別
     * @return string 次の学年
     */
    private function calculateNextGrade(string $currentGrade, string $gender): string
    {
        // B3は10月に引退（executeOctoberRetirement）、4月には残っていればOBへ
        // M1/M2は最初からOB扱いだが、4月更新でも念のためOBへ
        $gradeMap = [
            '1'  => '2',
            '2'  => '3',
            '3'  => $gender === 'male' ? 'OB' : 'OG',
            '4'  => $gender === 'male' ? 'OB' : 'OG',
            'M1' => $gender === 'male' ? 'OB' : 'OG',
            'M2' => $gender === 'male' ? 'OB' : 'OG',
        ];

        return $gradeMap[$currentGrade] ?? $currentGrade;
    }

    /**
     * 指定年度の会員数を取得
     *
     * @param int $year 年度
     * @return int 会員数
     */
    public function countByYear(int $year): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM members WHERE academic_year = ?",
            [$year]
        );
        return (int)($result['count'] ?? 0);
    }

    /**
     * 指定年度の会員を取得
     *
     * @param int $year 年度
     * @param string|null $status ステータス（オプション）
     * @return array 会員リスト
     */
    public function findByYear(int $year, ?string $status = null): array
    {
        if ($status) {
            return $this->db->fetchAll(
                "SELECT * FROM members WHERE academic_year = ? AND status = ? ORDER BY name_kana ASC",
                [$year, $status]
            );
        } else {
            return $this->db->fetchAll(
                "SELECT * FROM members WHERE academic_year = ? ORDER BY name_kana ASC",
                [$year]
            );
        }
    }
}

}
