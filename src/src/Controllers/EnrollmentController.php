<?php
/**
 * 入会フォームコントローラー（公開ページ）
 */
class EnrollmentController
{
    /**
     * 入会フォーム表示
     */
    public function form(array $params): void
    {
        // 入会受付中の年度があるか確認
        $academicYearModel = new AcademicYear();
        $openYear = $academicYearModel->getEnrollmentOpenYear();
        if (!$openYear) {
            $this->render('enroll/form', ['enrollmentClosed' => true, 'savedData' => null]);
            return;
        }

        // セッションに保存されたデータがあれば渡す（確認画面から戻った場合）
        $savedData = $_SESSION['enrollment_data'] ?? null;
        $this->render('enroll/form', ['enrollmentClosed' => false, 'savedData' => $savedData, 'openYear' => $openYear]);
    }

    /**
     * 入会申請確認画面表示
     */
    public function confirm(array $params): void
    {
        // POSTデータがセッションに保存されているか確認
        if (!isset($_SESSION['enrollment_data'])) {
            Response::redirect('/enroll');
            return;
        }

        $data = $_SESSION['enrollment_data'];
        $this->render('enroll/confirm', ['data' => $data]);
    }

    /**
     * 入会申請送信（確認画面からPOST）
     */
    public function submit(array $params): void
    {
        $action = Request::get('action');

        // 申請送信の場合（確認画面から）
        if ($action === 'submit') {
            // セッションからデータを取得
            if (!isset($_SESSION['enrollment_data'])) {
                Response::json([
                    'success' => false,
                    'error' => 'セッションが切れました。最初からやり直してください。'
                ]);
                return;
            }
            $data = $_SESSION['enrollment_data'];

            // 入会受付中の年度を取得（送信時点で再確認）
            $academicYearModel = new AcademicYear();
            $openYear = $academicYearModel->getEnrollmentOpenYear();
            if (!$openYear) {
                Response::json([
                    'success' => false,
                    'error' => '現在入会受付は行っていません。'
                ]);
                return;
            }

            try {

                // 学年の計算（入学年度から現在の学年を算出）
                $parserService = new StudentIdParserService();
                $currentYear = (int)date('Y');
                $currentMonth = (int)date('n');
                $grade = $parserService->calculateGrade((int)$data['enrollment_year'], $currentYear, $currentMonth);
                $data['grade'] = (string)$grade;

                // 入会受付中の年度を設定
                $data['academic_year'] = $openYear['year'];

                // ステータスは「承認待ち」で登録
                $data['status'] = 'pending';

                // 会員データを登録
                $memberModel = new Member();
                $memberId = $memberModel->create($data);

                // セッションクリア
                unset($_SESSION['enrollment_data']);

                Response::json([
                    'success' => true,
                    'redirect' => '/enroll/complete',
                    'message' => '入会申請を受け付けました'
                ]);

            } catch (Exception $e) {
                Response::json([
                    'success' => false,
                    'error' => '入会申請の送信に失敗しました: ' . $e->getMessage()
                ]);
            }
            return;
        }

        // 確認画面へ進む場合（入力フォームから）
        if ($action === 'confirm') {
            // 入力データの取得
            $data = [
                'name_kanji' => Request::get('name_kanji'),
                'name_kana' => Request::get('name_kana'),
                'gender' => Request::get('gender'),
                'birthdate' => Request::get('birthdate'),
                'student_id' => Request::get('student_id'),
                'faculty' => Request::get('faculty'),
                'department' => Request::get('department'),
                'enrollment_year' => Request::get('enrollment_year'),
                'phone' => Request::get('phone'),
                'address' => Request::get('address'),
                'emergency_contact' => Request::get('emergency_contact'),
                'email' => Request::get('email'),
                'line_name' => Request::get('line_name'),
                'allergy' => Request::get('allergy'),
                'sns_allowed' => Request::get('sns_allowed', 0),
                'sports_registration_no' => Request::get('sports_registration_no'),
            ];

            // バリデーション
            $errors = $this->validate($data);
            if (!empty($errors)) {
                Response::json([
                    'success' => false,
                    'errors' => $errors
                ]);
                return;
            }

            // セッションに保存
            $_SESSION['enrollment_data'] = $data;
            Response::json([
                'success' => true,
                'redirect' => '/enroll/confirm'
            ]);
            return;
        }

        Response::json([
            'success' => false,
            'error' => '不正なリクエストです'
        ]);
    }

    /**
     * 入会完了画面表示
     */
    public function complete(array $params): void
    {
        $this->render('enroll/complete');
    }

    /**
     * バリデーション
     */
    private function validate(array $data): array
    {
        $errors = [];

        // 必須項目チェック
        if (empty($data['name_kanji'])) {
            $errors['name_kanji'] = '名前（漢字）は必須です';
        }
        if (empty($data['name_kana'])) {
            $errors['name_kana'] = '名前（カナ）は必須です';
        }
        if (empty($data['gender'])) {
            $errors['gender'] = '性別は必須です';
        } elseif (!in_array($data['gender'], ['male', 'female'])) {
            $errors['gender'] = '性別の値が不正です';
        }
        if (empty($data['birthdate'])) {
            $errors['birthdate'] = '生年月日は必須です';
        }
        if (empty($data['student_id'])) {
            $errors['student_id'] = '学籍番号は必須です';
        } else {
            // 学籍番号の形式チェック
            $parserService = new StudentIdParserService();
            if (!$parserService->isValidFormat($data['student_id'])) {
                $errors['student_id'] = '学籍番号の形式が正しくありません（例: 1Y25F158-5）';
            } else {
                // 重複チェック
                $memberModel = new Member();
                if ($memberModel->existsByStudentId($data['student_id'])) {
                    $errors['student_id'] = 'この学籍番号は既に登録されています';
                }
            }
        }
        if (empty($data['faculty'])) {
            $errors['faculty'] = '学部は必須です';
        }
        if (empty($data['department'])) {
            $errors['department'] = '学科/学系は必須です';
        }
        if (empty($data['enrollment_year'])) {
            $errors['enrollment_year'] = '入学年度は必須です';
        }
        if (empty($data['phone'])) {
            $errors['phone'] = '電話番号は必須です';
        } elseif (!preg_match('/^\d{2,4}-\d{2,4}-\d{4}$/', $data['phone'])) {
            $errors['phone'] = '電話番号はハイフン区切りで入力してください（例: 090-1234-5678）';
        }
        if (empty($data['address'])) {
            $errors['address'] = '住所は必須です';
        }
        if (empty($data['emergency_contact'])) {
            $errors['emergency_contact'] = '緊急連絡先は必須です';
        } elseif (!preg_match('/^\d{2,4}-\d{2,4}-\d{4}$/', $data['emergency_contact'])) {
            $errors['emergency_contact'] = '緊急連絡先はハイフン区切りで入力してください';
        }
        if (empty($data['line_name'])) {
            $errors['line_name'] = 'LINE名は必須です';
        }

        // アレルギーは任意項目のため、チェック不要

        return $errors;
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
