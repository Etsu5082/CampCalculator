<?php
/**
 * ページコントローラー（HTML表示用）
 */
class PageController
{
    /**
     * トップページ（リダイレクト）
     */
    public function index(array $params): void
    {
        if (Auth::check()) {
            Response::redirect('/camps');
        } else {
            Response::redirect('/login');
        }
    }

    /**
     * ログインページ
     */
    public function login(array $params): void
    {
        if (Auth::check()) {
            Response::redirect('/camps');
        }

        $this->render('auth/login');
    }

    /**
     * 合宿一覧ページ
     */
    public function camps(array $params): void
    {
        Auth::requireAuth();
        $this->render('camps/index');
    }

    /**
     * 合宿詳細ページ
     */
    public function campDetail(array $params): void
    {
        Auth::requireAuth();
        $campId = (int)$params['id'];

        $campModel = new Camp();
        $camp = $campModel->find($campId);

        if (!$camp) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('camps/detail', ['campId' => $campId, 'camp' => $camp]);
    }

    /**
     * 計算結果ページ
     */
    public function result(array $params): void
    {
        Auth::requireAuth();
        $campId = (int)$params['id'];

        $campModel = new Camp();
        $camp = $campModel->find($campId);

        if (!$camp) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('results/show', ['campId' => $campId, 'camp' => $camp]);
    }

    /**
     * 途中参加・途中抜けスケジュール表ページ
     */
    public function partialSchedule(array $params): void
    {
        Auth::requireAuth();
        $campId = (int)$params['id'];

        $campModel = new Camp();
        $camp = $campModel->find($campId);

        if (!$camp) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('results/partial-schedule', ['campId' => $campId, 'camp' => $camp]);
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
