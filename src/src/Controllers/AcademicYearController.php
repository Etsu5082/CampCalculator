<?php
/**
 * 年度管理コントローラー
 */

if (!class_exists('AcademicYearController')) {

class AcademicYearController
{
    private AcademicYear $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new AcademicYear();
    }

    /**
     * 年度一覧取得
     */
    public function index(array $params): void
    {
        try {
            $years = $this->model->getAll();

            Response::success([
                'years' => $years,
            ]);
        } catch (Exception $e) {
            Response::error('年度一覧の取得に失敗しました: ' . $e->getMessage(), 500, 'FETCH_ERROR');
        }
    }

    /**
     * 現在年度取得
     */
    public function getCurrent(array $params): void
    {
        try {
            $currentYear = $this->model->getCurrent();

            if (!$currentYear) {
                Response::error('現在年度が設定されていません', 404, 'NOT_FOUND');
                return;
            }

            Response::success($currentYear);
        } catch (Exception $e) {
            Response::error('現在年度の取得に失敗しました: ' . $e->getMessage(), 500, 'FETCH_ERROR');
        }
    }

    /**
     * 年度作成
     */
    public function store(array $params): void
    {
        $year = (int)Request::get('year');
        $startDate = Request::get('start_date');
        $endDate = Request::get('end_date');
        $isCurrent = (int)Request::get('is_current', 0);
        $enrollmentOpen = (int)Request::get('enrollment_open', 0);

        // バリデーション
        if (!$year || $year < 2020 || $year > 2100) {
            Response::error('年度は2020～2100の範囲で指定してください', 400, 'VALIDATION_ERROR');
            return;
        }

        if ($this->model->exists($year)) {
            Response::error('この年度は既に存在します', 400, 'DUPLICATE_ERROR');
            return;
        }

        try {
            $id = $this->model->create([
                'year' => $year,
                'start_date' => $startDate ?: "{$year}-04-01",
                'end_date' => $endDate ?: ($year + 1) . "-03-31",
                'is_current' => $isCurrent,
                'enrollment_open' => $enrollmentOpen,
            ]);

            Response::success([
                'id' => $id,
                'message' => "{$year}年度を作成しました",
            ]);
        } catch (Exception $e) {
            Response::error('年度の作成に失敗しました: ' . $e->getMessage(), 500, 'CREATE_ERROR');
        }
    }

    /**
     * 次年度を自動作成
     */
    public function createNext(array $params): void
    {
        try {
            $id = $this->model->createNextYear();

            Response::success([
                'id' => $id,
                'message' => '次年度を作成しました',
            ]);
        } catch (Exception $e) {
            Response::error('次年度の作成に失敗しました: ' . $e->getMessage(), 500, 'CREATE_ERROR');
        }
    }

    /**
     * 現在年度を切り替え
     */
    public function setCurrent(array $params): void
    {
        $year = (int)Request::get('year');

        if (!$year) {
            Response::error('年度を指定してください', 400, 'VALIDATION_ERROR');
            return;
        }

        if (!$this->model->exists($year)) {
            Response::error('指定された年度が存在しません', 404, 'NOT_FOUND');
            return;
        }

        try {
            $this->model->setCurrentYear($year);

            Response::success([
                'message' => "{$year}年度を現在年度に設定しました",
            ]);
        } catch (Exception $e) {
            Response::error('現在年度の設定に失敗しました: ' . $e->getMessage(), 500, 'UPDATE_ERROR');
        }
    }

    /**
     * 入会受付の開始/停止
     */
    public function setEnrollmentOpen(array $params): void
    {
        $year = (int)Request::get('year');
        $open = (bool)Request::get('open');

        if (!$year) {
            Response::error('年度を指定してください', 400, 'VALIDATION_ERROR');
            return;
        }

        try {
            $this->model->setEnrollmentOpen($year, $open);

            Response::success([
                'message' => $open ? '入会受付を開始しました' : '入会受付を停止しました',
            ]);
        } catch (Exception $e) {
            Response::error('入会受付の設定に失敗しました: ' . $e->getMessage(), 500, 'UPDATE_ERROR');
        }
    }

    /**
     * 年度管理ページ表示
     */
    public function indexPage(array $params): void
    {
        $this->render('academic-years/index');
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

        include VIEWS_PATH . '/layouts/main.php';
    }
}

}
