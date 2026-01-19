<?php
/**
 * 合宿コントローラー
 */
class CampController
{
    private Camp $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new Camp();
    }

    /**
     * 一覧取得
     */
    public function index(array $params): void
    {
        $camps = $this->model->all();

        // 参加者数を追加
        $participantModel = new Participant();
        foreach ($camps as &$camp) {
            $camp['participant_count'] = $participantModel->countByCampId($camp['id']);
        }

        Response::success($camps);
    }

    /**
     * 詳細取得
     */
    public function show(array $params): void
    {
        $id = (int)$params['id'];
        $camp = $this->model->find($id);

        if (!$camp) {
            Response::error('合宿が見つかりません', 404, 'NOT_FOUND');
        }

        // 関連データを取得
        $timeSlotModel = new TimeSlot();
        $participantModel = new Participant();
        $expenseModel = new Expense();

        $camp['time_slots'] = $timeSlotModel->getByCampId($id);
        $camp['participants'] = $participantModel->getByCampId($id);
        $camp['expenses'] = $expenseModel->getByCampId($id);

        Response::success($camp);
    }

    /**
     * 新規作成
     */
    public function store(array $params): void
    {
        $errors = Request::validate([
            'name' => 'required|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'nights' => 'required|integer',
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = Request::only([
            'name', 'start_date', 'end_date', 'nights',
            'lodging_fee_per_night',
            'breakfast_add_price', 'breakfast_remove_price',
            'lunch_add_price', 'lunch_remove_price',
            'dinner_add_price', 'dinner_remove_price',
            'insurance_fee', 'court_fee_per_unit', 'gym_fee_per_unit', 'banquet_fee_per_person', 'first_day_lunch_included',
            'bus_fee_round_trip', 'bus_fee_separate',
            'bus_fee_outbound', 'bus_fee_return',
            'highway_fee_outbound', 'highway_fee_return',
            'use_rental_car', 'rental_car_fee', 'rental_car_highway_fee', 'rental_car_capacity',
        ]);

        try {
            $id = $this->model->create($data);

            // デフォルトのタイムスロットを生成（コート単価と体育館単価を渡す）
            $timeSlotModel = new TimeSlot();
            $courtFeePerUnit = isset($data['court_fee_per_unit']) ? (int)$data['court_fee_per_unit'] : null;
            $gymFeePerUnit = isset($data['gym_fee_per_unit']) ? (int)$data['gym_fee_per_unit'] : null;
            $timeSlotModel->createDefaultSlots($id, (int)$data['nights'], $courtFeePerUnit, $gymFeePerUnit);

            $camp = $this->model->find($id);
            Response::success($camp, '合宿を作成しました');

        } catch (Exception $e) {
            Response::error('合宿の作成に失敗しました: ' . $e->getMessage(), 500, 'CREATE_ERROR');
        }
    }

    /**
     * 更新
     */
    public function update(array $params): void
    {
        $id = (int)$params['id'];

        $existing = $this->model->find($id);
        if (!$existing) {
            Response::error('合宿が見つかりません', 404, 'NOT_FOUND');
        }

        $data = Request::only([
            'name', 'start_date', 'end_date', 'nights',
            'lodging_fee_per_night',
            'breakfast_add_price', 'breakfast_remove_price',
            'lunch_add_price', 'lunch_remove_price',
            'dinner_add_price', 'dinner_remove_price',
            'insurance_fee', 'court_fee_per_unit', 'gym_fee_per_unit', 'banquet_fee_per_person', 'first_day_lunch_included',
            'bus_fee_round_trip', 'bus_fee_separate',
            'bus_fee_outbound', 'bus_fee_return',
            'highway_fee_outbound', 'highway_fee_return',
            'use_rental_car', 'rental_car_fee', 'rental_car_highway_fee', 'rental_car_capacity',
        ]);

        try {
            $this->model->update($id, $data);
            $camp = $this->model->find($id);
            Response::success($camp, '合宿を更新しました');

        } catch (Exception $e) {
            Response::error('合宿の更新に失敗しました: ' . $e->getMessage(), 500, 'UPDATE_ERROR');
        }
    }

    /**
     * 削除
     */
    public function destroy(array $params): void
    {
        $id = (int)$params['id'];

        $existing = $this->model->find($id);
        if (!$existing) {
            Response::error('合宿が見つかりません', 404, 'NOT_FOUND');
        }

        try {
            $this->model->delete($id);
            Response::success([], '合宿を削除しました');

        } catch (Exception $e) {
            Response::error('合宿の削除に失敗しました: ' . $e->getMessage(), 500, 'DELETE_ERROR');
        }
    }

    /**
     * 複製
     */
    public function duplicate(array $params): void
    {
        $id = (int)$params['id'];

        $existing = $this->model->find($id);
        if (!$existing) {
            Response::error('合宿が見つかりません', 404, 'NOT_FOUND');
        }

        $overrideData = Request::only(['name', 'start_date', 'end_date']);

        try {
            $newId = $this->model->duplicate($id, $overrideData);

            if (!$newId) {
                Response::error('合宿の複製に失敗しました', 500, 'DUPLICATE_ERROR');
            }

            $camp = $this->model->find($newId);
            Response::success($camp, '合宿を複製しました');

        } catch (Exception $e) {
            Response::error('合宿の複製に失敗しました: ' . $e->getMessage(), 500, 'DUPLICATE_ERROR');
        }
    }
}
