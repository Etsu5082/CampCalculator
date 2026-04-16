<?php
/**
 * 継続入会フォームコントローラー（公開ページ）
 */
class RenewalController
{
    /**
     * 次年度の学年を計算
     * 学籍番号の入学年度と登録先年度から算出
     * - 1年目→1, 2年目→2, 3年目→3, 4年目以上→OB/OG
     * - 10月以降は3年生もOB/OG扱い
     * - 学籍番号が解析できない場合は既存gradeを1つ進める
     *
     * @param array  $member   会員データ
     * @param int    $nextYear 登録先年度
     * @return string 次年度の学年
     */
    private function calculateNextGrade(array $member, int $nextYear): string
    {
        $gender = $member['gender'];
        $ob = $gender === 'male' ? 'OB' : 'OG';
        $month = (int)date('n');

        // 学籍番号から入学年度を取得できる場合
        $enrollmentYear = $member['enrollment_year'] ?? null;

        if ($enrollmentYear) {
            // 登録先年度における在籍年数（1始まり）
            $yearsEnrolled = $nextYear - (int)$enrollmentYear + 1;

            if ($yearsEnrolled >= 4) {
                return $ob;
            }
            // 入学年度+2年後の10月を過ぎていれば3年生もOB/OG
            // 例: 2023年入学 → 2025年10月1日以降はOB/OG
            $retirementDate = mktime(0, 0, 0, 10, 1, (int)$enrollmentYear + 2);
            if ($yearsEnrolled === 3 && time() >= $retirementDate) {
                return $ob;
            }
            return (string)max(1, $yearsEnrolled);
        }

        // enrollment_year がない場合は既存gradeをマップで進める
        $gradeMap = [
            '1'  => '2',
            '2'  => '3',
            '3'  => ($month >= 10) ? $ob : '3',
            '4'  => $ob,
            'M1' => $ob,
            'M2' => $ob,
            'OB' => 'OB',
            'OG' => 'OG',
        ];
        return $gradeMap[$member['grade']] ?? $member['grade'];
    }

    /**
     * 入会受付中年度から登録先年度と検索対象年度を取得
     * 登録先 = 入会受付中の年度
     * 検索対象 = 登録先 - 1（前年度の名簿）
     */
    private function getYearInfo(): ?array
    {
        $ayModel = new AcademicYear();
        $openYear = $ayModel->getEnrollmentOpenYear();

        if (!$openYear) {
            return null;
        }

        $nextYear = (int)$openYear['year'];
        $previousYear = $nextYear - 1;

        return [
            'year'         => $nextYear,     // 登録先年度
            'previousYear' => $previousYear, // 検索対象年度（前年度）
        ];
    }

    /**
     * 会員検索画面表示
     */
    public function search(array $params): void
    {
        $yearInfo = $this->getYearInfo();

        if (!$yearInfo) {
            $this->render('renew/search', [
                'currentYear'  => null,
                'previousYear' => null,
            ]);
            return;
        }

        $this->render('renew/search', [
            'currentYear'  => ['year' => $yearInfo['year']],
            'previousYear' => $yearInfo['previousYear'],
        ]);
    }

    /**
     * 会員検索API
     */
    public function searchMembers(array $params): void
    {
        $name = Request::get('name', '');

        if (empty($name)) {
            Response::json([
                'success' => false,
                'error' => '名前を入力してください'
            ]);
            return;
        }

        $yearInfo = $this->getYearInfo();

        if (!$yearInfo) {
            Response::json([
                'success' => false,
                'error' => '現在、入会受付を行っていません'
            ]);
            return;
        }

        $nextYear     = $yearInfo['year'];
        $previousYear = $yearInfo['previousYear'];

        // 前年度の名簿から検索
        $memberModel = new Member();
        $members = $memberModel->searchPreviousYear($name, $previousYear);

        // 既に新年度に登録済みかチェック
        foreach ($members as &$member) {
            $existing = $memberModel->findByStudentIdAndYear(
                $member['student_id'],
                $nextYear
            );
            $member['already_renewed'] = ($existing !== null);
        }

        Response::json([
            'success'      => true,
            'members'      => $members,
            'currentYear'  => $nextYear,
            'previousYear' => $previousYear,
        ]);
    }

    /**
     * 情報確認・編集画面
     */
    public function confirm(array $params): void
    {
        $memberId = Request::get('member_id');

        if (!$memberId) {
            Response::redirect('/renew');
            return;
        }

        // 会員情報を取得
        $memberModel = new Member();
        $member = $memberModel->find((int)$memberId);

        if (!$member) {
            $this->render('error', [
                'message' => '会員が見つかりません'
            ]);
            return;
        }

        // 入会受付中年度を取得
        $yearInfo = $this->getYearInfo();
        if (!$yearInfo) {
            $this->render('error', [
                'message' => '現在、入会受付を行っていません'
            ]);
            return;
        }
        $nextYear = $yearInfo['year'];

        // 次の学年を計算
        $nextGrade = $this->calculateNextGrade($member, $nextYear);

        // セッションに保存
        $_SESSION['renewal_data'] = [
            'original_member_id' => $member['id'],
            'member' => $member,
            'next_grade' => $nextGrade,
            'current_year' => $nextYear,
        ];

        $this->render('renew/confirm', [
            'member' => $member,
            'nextGrade' => $nextGrade,
            'currentYear' => ['year' => $nextYear],
        ]);
    }

    /**
     * 最終確認画面
     */
    public function review(array $params): void
    {
        // セッションチェック
        if (!isset($_SESSION['renewal_data'])) {
            Response::redirect('/renew');
            return;
        }

        // POSTデータで更新があれば適用
        $member = $_SESSION['renewal_data']['member'];
        $updatedFields = [
            'name_kanji', 'name_kana', 'gender', 'grade', 'faculty', 'department',
            'student_id', 'phone', 'address', 'emergency_contact', 'birthdate',
            'allergy', 'line_name', 'sns_allowed', 'sports_registration_no', 'email'
        ];

        foreach ($updatedFields as $field) {
            if (Request::has($field)) {
                $member[$field] = Request::get($field);
            }
        }

        $_SESSION['renewal_data']['member'] = $member;

        // gradeが変更された場合はnext_gradeを再計算
        $currentYear = (int)$_SESSION['renewal_data']['current_year'];
        $_SESSION['renewal_data']['next_grade'] = $this->calculateNextGrade($member, $currentYear);

        $this->render('renew/review', [
            'member' => $member,
            'currentYear' => $_SESSION['renewal_data']['current_year'],
        ]);
    }

    /**
     * 継続登録処理
     */
    public function submit(array $params): void
    {
        // セッションチェック
        if (!isset($_SESSION['renewal_data'])) {
            Response::json([
                'success' => false,
                'error' => 'セッションが無効です。最初からやり直してください。'
            ]);
            return;
        }

        $data = $_SESSION['renewal_data'];
        $member = $data['member'];
        $currentYear = (int)$data['current_year'];

        try {
            $memberModel = new Member();

            // 既に登録済みかチェック
            $existing = $memberModel->findByStudentIdAndYear(
                $member['student_id'],
                $currentYear
            );

            if ($existing) {
                Response::json([
                    'success' => false,
                    'error' => 'この学籍番号は既に' . $currentYear . '年度に登録されています'
                ]);
                return;
            }

            // 新年度のデータを作成
            $newMemberData = [
                'name_kanji' => $member['name_kanji'],
                'name_kana' => $member['name_kana'],
                'gender' => $member['gender'],
                'grade' => $data['next_grade'],  // confirm()で計算した次年度の学年を使う
                'faculty' => $member['faculty'],
                'department' => $member['department'],
                'student_id' => $member['student_id'],
                'phone' => $member['phone'],
                'address' => $member['address'],
                'emergency_contact' => $member['emergency_contact'],
                'birthdate' => $member['birthdate'],
                'allergy' => $member['allergy'] ?? null,
                'line_name' => $member['line_name'],
                'sns_allowed' => $member['sns_allowed'] ?? 1,
                'sports_registration_no' => $member['sports_registration_no'] ?? null,
                'email' => $member['email'] ?? null,
                'status' => Member::STATUS_ACTIVE,  // 継続入会なので即座にactive
                'department_not_set' => (!empty($member['department']) ? 0 : ($member['department_not_set'] ?? 0)),
                'enrollment_year' => $member['enrollment_year'] ?? null,
                'academic_year' => $currentYear,
            ];

            // 挿入
            $sql = "INSERT INTO members (
                name_kanji, name_kana, gender, grade, faculty, department,
                student_id, phone, address, emergency_contact, birthdate,
                allergy, line_name, sns_allowed, sports_registration_no, email,
                status, department_not_set, enrollment_year, academic_year
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $db = Database::getInstance();
            $newMemberId = $db->insert($sql, [
                $newMemberData['name_kanji'],
                $newMemberData['name_kana'],
                $newMemberData['gender'],
                $newMemberData['grade'],
                $newMemberData['faculty'],
                $newMemberData['department'],
                $newMemberData['student_id'],
                $newMemberData['phone'],
                $newMemberData['address'],
                $newMemberData['emergency_contact'],
                $newMemberData['birthdate'],
                $newMemberData['allergy'],
                $newMemberData['line_name'],
                $newMemberData['sns_allowed'],
                $newMemberData['sports_registration_no'],
                $newMemberData['email'],
                $newMemberData['status'],
                $newMemberData['department_not_set'],
                $newMemberData['enrollment_year'],
                $newMemberData['academic_year'],
            ]);

            // セッションクリア
            unset($_SESSION['renewal_data']);

            Response::json([
                'success' => true,
                'redirect' => '/renew/complete',
                'message' => $currentYear . '年度への継続登録が完了しました'
            ]);

        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'error' => '継続登録に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 完了画面
     */
    public function complete(array $params): void
    {
        $this->render('renew/complete');
    }

    /**
     * ビューのレンダリング
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        $config = require CONFIG_PATH . '/app.php';
        $appName = $config['name'];

        ob_start();
        include VIEWS_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        // 公開ページなので認証不要のレイアウト
        include VIEWS_PATH . '/layouts/public.php';
    }
}
