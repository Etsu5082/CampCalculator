<?php
/**
 * 会員ポータルコントローラー（公開ページ）
 */
class PortalController
{
    /**
     * ポータルトップページ表示
     */
    public function index(array $params): void
    {
        // 募集中の合宿を取得
        $campTokenModel = new CampToken();
        $activeCamps = $campTokenModel->getActiveCampsWithTokens();

        $this->render('portal/index', [
            'activeCamps' => $activeCamps
        ]);
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
