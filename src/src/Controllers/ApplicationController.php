<?php
/**
 * 合宿申し込みコントローラー（公開・認証不要）
 */
class ApplicationController
{
    private CampToken $tokenModel;
    private Camp $campModel;
    private Member $memberModel;
    private CampApplication $applicationModel;
    private AcademicYear $academicYearModel;

    public function __construct()
    {
        // 公開ページなので認証不要
        $this->tokenModel = new CampToken();
        $this->campModel = new Camp();
        $this->memberModel = new Member();
        $this->applicationModel = new CampApplication();
        $this->academicYearModel = new AcademicYear();
    }

    /**
     * 申し込みフォーム表示（会員検索画面）
     */
    public function form(array $params): void
    {
        $token = $params['token'];

        // トークンの有効性チェック
        if (!$this->tokenModel->isValid($token)) {
            $this->showError('無効な申し込みURLです。URLが正しいか、有効期限内かをご確認ください。');
            return;
        }

        // トークン情報と合宿情報を取得
        $tokenData = $this->tokenModel->findByToken($token);
        $camp = $this->campModel->find($tokenData['camp_id']);

        if (!$camp) {
            $this->showError('合宿情報が見つかりません。');
            return;
        }

        // 検索画面を表示
        include VIEWS_PATH . '/apply/search.php';
    }

    /**
     * 会員検索API
     */
    public function searchMembers(array $params): void
    {
        $token = $params['token'];

        // トークンの有効性チェック
        if (!$this->tokenModel->isValid($token)) {
            Response::error('無効なトークンです', 400, 'INVALID_TOKEN');
            return;
        }

        $searchQuery = Request::get('search', '');

        if (empty($searchQuery)) {
            Response::success(['members' => []]);
            return;
        }

        // 会員検索（名前の部分一致、現役会員のみ）
        // enrollment_open中の年度の会員を優先するため、年度フィルターはかけず status=active で絞る
        $openYear = $this->academicYearModel->getEnrollmentOpenYear();
        $filters = [
            'search' => $searchQuery,
            'status' => 'active',
        ];
        if ($openYear) {
            $filters['academic_year'] = (int)$openYear['year'];
        }

        $members = $this->memberModel->search($filters, 10, 0); // 最大10件まで

        // 必要な情報のみ返す
        $results = array_map(function($member) {
            return [
                'id' => $member['id'],
                'name_kanji' => $member['name_kanji'],
                'name_kana' => $member['name_kana'],
                'grade' => $member['grade'],
                'gender' => $member['gender'],
                'faculty' => $member['faculty'],
                'department' => $member['department'],
                'student_id' => $member['student_id'],
            ];
        }, $members);

        Response::success(['members' => $results]);
    }

    /**
     * 情報確認画面表示
     */
    public function confirmInfo(array $params): void
    {
        $token = $params['token'];

        // トークンの有効性チェック
        if (!$this->tokenModel->isValid($token)) {
            $this->showError('無効な申し込みURLです。');
            return;
        }

        $memberId = Request::get('member_id');
        if (!$memberId) {
            Response::redirect("/apply/{$token}");
            return;
        }

        // 会員情報取得
        $member = $this->memberModel->find($memberId);
        if (!$member || $member['status'] !== 'active') {
            $this->showError('会員情報が見つかりません。');
            return;
        }

        // トークン情報と合宿情報を取得
        $tokenData = $this->tokenModel->findByToken($token);
        $camp = $this->campModel->find($tokenData['camp_id']);

        // 既に申し込み済みかチェック
        $hasApplied = $this->applicationModel->hasApplied($camp['id'], $memberId);

        include VIEWS_PATH . '/apply/confirm-info.php';
    }

    /**
     * 日程選択画面表示
     */
    public function schedule(array $params): void
    {
        $token = $params['token'];

        // トークンの有効性チェック
        if (!$this->tokenModel->isValid($token)) {
            $this->showError('無効な申し込みURLです。');
            return;
        }

        $memberId = Request::get('member_id');
        if (!$memberId) {
            Response::redirect("/apply/{$token}");
            return;
        }

        // 会員情報取得
        $member = $this->memberModel->find($memberId);
        if (!$member || $member['status'] !== 'active') {
            $this->showError('会員情報が見つかりません。');
            return;
        }

        // トークン情報と合宿情報を取得
        $tokenData = $this->tokenModel->findByToken($token);
        $camp = $this->campModel->find($tokenData['camp_id']);

        include VIEWS_PATH . '/apply/schedule.php';
    }

    /**
     * 最終確認画面表示
     */
    public function review(array $params): void
    {
        $token = $params['token'];

        // トークンの有効性チェック
        if (!$this->tokenModel->isValid($token)) {
            $this->showError('無効な申し込みURLです。');
            return;
        }

        $memberId = Request::get('member_id');
        $joinDay = Request::get('join_day');
        $joinTiming = Request::get('join_timing');
        $leaveDay = Request::get('leave_day');
        $leaveTiming = Request::get('leave_timing');
        $useOutboundBus = Request::get('use_outbound_bus');
        $useReturnBus = Request::get('use_return_bus');

        if (!$memberId) {
            Response::redirect("/apply/{$token}");
            return;
        }

        // 会員情報取得
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            $this->showError('会員情報が見つかりません。');
            return;
        }

        // トークン情報と合宿情報を取得
        $tokenData = $this->tokenModel->findByToken($token);
        $camp = $this->campModel->find($tokenData['camp_id']);

        include VIEWS_PATH . '/apply/review.php';
    }

    /**
     * 申し込み送信
     */
    public function submit(array $params): void
    {
        $token = $params['token'];

        // トークンの有効性チェック
        if (!$this->tokenModel->isValid($token)) {
            Response::error('無効なトークンです', 400, 'INVALID_TOKEN');
            return;
        }

        // トークン情報取得
        $tokenData = $this->tokenModel->findByToken($token);
        $campId = $tokenData['camp_id'];

        // バリデーション
        $errors = Request::validate([
            'member_id' => 'required|integer',
            'join_day' => 'required|integer',
            'join_timing' => 'required',
            'leave_day' => 'required|integer',
            'leave_timing' => 'required',
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        $data = Request::only([
            'member_id', 'join_day', 'join_timing', 'leave_day', 'leave_timing',
            'use_outbound_bus', 'use_return_bus'
        ]);

        $data['camp_id'] = $campId;
        $data['use_outbound_bus'] = isset($data['use_outbound_bus']) && $data['use_outbound_bus'] ? 1 : 0;
        $data['use_return_bus'] = isset($data['use_return_bus']) && $data['use_return_bus'] ? 1 : 0;

        // 会員情報取得
        $member = $this->memberModel->find($data['member_id']);
        if (!$member || $member['status'] !== 'active') {
            Response::error('会員情報が見つかりません', 404, 'MEMBER_NOT_FOUND');
            return;
        }

        try {
            // 既に申し込み済みの場合は上書き
            $existingApplication = $this->applicationModel->findByCampAndMember($campId, $data['member_id']);

            if ($existingApplication) {
                // 既存の申し込みを更新
                $participantModel = new Participant();

                // まず既存の参加者レコードを削除（新しく作り直す）
                if ($existingApplication['participant_id']) {
                    $participantModel->delete($existingApplication['participant_id']);
                }

                // 学年の変換（会員マスタのgradeをparticipantsのgradeに変換）
                $participantGrade = null;
                if (in_array($member['grade'], ['1', '2', '3', '4'])) {
                    $participantGrade = (int)$member['grade'];
                } elseif (in_array($member['grade'], ['OB', 'OG'])) {
                    $participantGrade = 0;
                }

                // 新しい参加者レコードを作成
                $participantId = $participantModel->create([
                    'camp_id' => $campId,
                    'name' => $member['name_kanji'],
                    'grade' => $participantGrade,
                    'gender' => $member['gender'],
                    'join_day' => $data['join_day'],
                    'join_timing' => $data['join_timing'],
                    'leave_day' => $data['leave_day'],
                    'leave_timing' => $data['leave_timing'],
                    'use_outbound_bus' => $data['use_outbound_bus'],
                    'use_return_bus' => $data['use_return_bus'],
                    'use_rental_car' => $data['use_rental_car'] ?? 0,
                ]);

                // 既存の申し込みレコードに新しいparticipant_idを紐付け
                $this->applicationModel->update($existingApplication['id'], [
                    'participant_id' => $participantId,
                    'join_day' => $data['join_day'],
                    'join_timing' => $data['join_timing'],
                    'leave_day' => $data['leave_day'],
                    'leave_timing' => $data['leave_timing'],
                    'use_outbound_bus' => $data['use_outbound_bus'],
                    'use_return_bus' => $data['use_return_bus'],
                    'status' => 'submitted',
                ]);

                Response::success([
                    'application_id' => $existingApplication['id'],
                    'participant_id' => $participantId,
                    'member' => $member,
                ], '申し込みを更新しました');
            } else {
                // 新規申し込み
                $result = $this->applicationModel->createWithParticipant($data);

                if (!$result['success']) {
                    Response::error('申し込みに失敗しました: ' . $result['error'], 500, 'CREATE_ERROR');
                    return;
                }

                Response::success([
                    'application_id' => $result['application_id'],
                    'participant_id' => $result['participant_id'],
                    'member' => $member,
                ], '申し込みを受け付けました');
            }

        } catch (Exception $e) {
            Response::error('申し込み処理中にエラーが発生しました: ' . $e->getMessage(), 500, 'ERROR');
        }
    }

    /**
     * 申し込み完了画面表示
     */
    public function complete(array $params): void
    {
        $token = $params['token'];

        // トークンの有効性チェック
        if (!$this->tokenModel->isValid($token)) {
            $this->showError('無効な申し込みURLです。');
            return;
        }

        $memberId = Request::get('member_id');
        if (!$memberId) {
            Response::redirect("/apply/{$token}");
            return;
        }

        // 会員情報取得
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            $this->showError('会員情報が見つかりません。');
            return;
        }

        // トークン情報と合宿情報を取得
        $tokenData = $this->tokenModel->findByToken($token);
        $camp = $this->campModel->find($tokenData['camp_id']);

        // 申し込み情報を取得
        $application = $this->applicationModel->findByCampAndMember($camp['id'], $memberId);

        include VIEWS_PATH . '/apply/complete.php';
    }

    /**
     * エラー画面を表示
     */
    private function showError(string $message): void
    {
        $errorMessage = $message;
        include VIEWS_PATH . '/apply/error.php';
    }
}
