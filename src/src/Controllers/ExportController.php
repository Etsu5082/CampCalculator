<?php
/**
 * 出力コントローラー
 */
class ExportController
{
    public function __construct()
    {
        Auth::requireAuth();
    }

    /**
     * PDF出力
     */
    public function pdf(array $params): void
    {
        $campId = (int)$params['id'];

        $campModel = new Camp();
        $camp = $campModel->find($campId);

        if (!$camp) {
            Response::error('合宿が見つかりません', 404, 'NOT_FOUND');
        }

        try {
            $exportService = new ExportService();
            $exportService->generatePdf($campId);

        } catch (Exception $e) {
            Response::error('PDF出力に失敗しました: ' . $e->getMessage(), 500, 'EXPORT_ERROR');
        }
    }

    /**
     * Excel出力
     */
    public function excel(array $params): void
    {
        $campId = (int)$params['id'];

        $campModel = new Camp();
        $camp = $campModel->find($campId);

        if (!$camp) {
            Response::error('合宿が見つかりません', 404, 'NOT_FOUND');
        }

        try {
            $exportService = new ExportService();
            $exportService->generateExcel($campId);

        } catch (Exception $e) {
            Response::error('Excel出力に失敗しました: ' . $e->getMessage(), 500, 'EXPORT_ERROR');
        }
    }
}
