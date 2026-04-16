<?php
/**
 * PDF解析サービス
 * 旅行代理店の契約書PDFから料金情報を抽出
 * スキャンPDFの場合はClaude Vision APIでOCR処理
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Smalot\PdfParser\Parser;

class PdfParserService
{
    private Parser $parser;
    private string $apiKey;
    private string $model;
    private bool $aiEnabled;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->parser = new Parser();

        // AI設定を読み込み
        $config = require CONFIG_PATH . '/ai.php';
        $this->apiKey = $config['anthropic_api_key'];
        $this->model = 'claude-sonnet-4-20250514'; // Vision対応モデル
        $this->aiEnabled = $config['enabled'] && !empty($this->apiKey);
    }

    /**
     * PDFファイルを解析して料金情報を抽出
     *
     * @param string $filePath PDFファイルのパス
     * @return array 抽出された料金情報
     * @throws Exception
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("PDFファイルが見つかりません: {$filePath}");
        }

        // PDFをパース
        $pdf = $this->parser->parseFile($filePath);
        $text = $pdf->getText();

        // テキストが十分に抽出できたかチェック（スキャンPDFかどうかの判定）
        $cleanText = preg_replace('/\s+/', '', $text);
        $isScannedPdf = mb_strlen($cleanText) < 100;

        if ($isScannedPdf) {
            // スキャンPDFの場合はClaude Vision APIでOCR
            if (!$this->aiEnabled) {
                throw new Exception("スキャンPDFの解析にはAI機能が必要です。APIキーを設定してください。");
            }
            return $this->parseWithVision($filePath);
        }

        // 契約書の種類を判定
        $contractType = $this->detectContractType($text);

        // 種類に応じたパース処理
        switch ($contractType) {
            case 'mainichi_reservation':
                return $this->parseMainichiReservation($text);
            case 'mainichi_bus':
                return $this->parseMainichiBus($text);
            case 'mainichi_travel':
                return $this->parseMainichiTravel($text);
            case 'cosmo_reservation':
                return $this->parseCosmoReservation($text);
            case 'cosmo_bus':
                return $this->parseCosmoBus($text);
            default:
                // 未知のフォーマットでもAIが有効ならVision APIで解析を試みる
                if ($this->aiEnabled) {
                    return $this->parseWithVision($filePath);
                }
                throw new Exception("未対応の契約書形式です");
        }
    }

    /**
     * Claude Vision APIを使ってPDFから情報を抽出
     *
     * @param string $filePath PDFファイルのパス
     * @return array
     * @throws Exception
     */
    private function parseWithVision(string $filePath): array
    {
        // PDFを画像に変換（各ページ）
        $images = $this->convertPdfToImages($filePath);

        if (empty($images)) {
            throw new Exception("PDFの画像変換に失敗しました");
        }

        // Claude Vision APIで解析
        $extractedData = $this->callVisionApi($images);

        // 一時画像ファイルを削除
        foreach ($images as $imagePath) {
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        return $extractedData;
    }

    /**
     * PDFを画像に変換
     *
     * @param string $filePath
     * @return array 画像ファイルパスの配列
     */
    private function convertPdfToImages(string $filePath): array
    {
        $images = [];
        $tempDir = sys_get_temp_dir();
        $baseName = uniqid('pdf_page_');

        // Imagickが使える場合
        if (extension_loaded('imagick')) {
            try {
                $imagick = new Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($filePath);

                $pageCount = $imagick->getNumberImages();
                for ($i = 0; $i < min($pageCount, 3); $i++) { // 最大3ページ
                    $imagick->setIteratorIndex($i);
                    $imagick->setImageFormat('png');
                    $outputPath = $tempDir . '/' . $baseName . '_' . $i . '.png';
                    $imagick->writeImage($outputPath);
                    $images[] = $outputPath;
                }
                $imagick->clear();
                $imagick->destroy();
                return $images;
            } catch (Exception $e) {
                // Imagickが失敗した場合は次の方法を試す
            }
        }

        // pdftopmが使える場合（poppler-utils）
        $outputPattern = $tempDir . '/' . $baseName;
        $command = "pdftoppm -png -r 150 -l 3 " . escapeshellarg($filePath) . " " . escapeshellarg($outputPattern) . " 2>&1";
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            // 生成された画像ファイルを収集
            for ($i = 1; $i <= 3; $i++) {
                $possiblePaths = [
                    $outputPattern . '-' . $i . '.png',
                    $outputPattern . '-0' . $i . '.png',
                    $outputPattern . '-00' . $i . '.png',
                ];
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $images[] = $path;
                        break;
                    }
                }
            }
            if (!empty($images)) {
                return $images;
            }
        }

        // どちらも使えない場合、PDFをそのままBase64エンコードして送信
        // Claude APIはPDFを直接受け付けないので、エラーを返す
        // ただし、お名前.comのサーバーにはImageMagickが入っている可能性があるので
        // 本番環境では動く可能性がある

        return [];
    }

    /**
     * Claude Vision APIを呼び出し
     *
     * @param array $imagePaths
     * @return array
     * @throws Exception
     */
    private function callVisionApi(array $imagePaths): array
    {
        $url = 'https://api.anthropic.com/v1/messages';

        // 画像をBase64エンコード
        $content = [];
        foreach ($imagePaths as $imagePath) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = 'image/png';

            $content[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $mimeType,
                    'data' => $imageData,
                ]
            ];
        }

        // テキストプロンプトを追加
        $content[] = [
            'type' => 'text',
            'text' => $this->getVisionPrompt()
        ];

        $data = [
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => 60
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('API接続エラー: ' . $error);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            throw new Exception('API エラー (' . $httpCode . '): ' . $errorMessage);
        }

        $result = json_decode($response, true);

        if (!isset($result['content'][0]['text'])) {
            throw new Exception('APIレスポンスの形式が不正です');
        }

        // JSONレスポンスをパース
        $responseText = $result['content'][0]['text'];
        return $this->parseVisionResponse($responseText);
    }

    /**
     * Vision APIに送るプロンプト
     *
     * @return string
     */
    private function getVisionPrompt(): string
    {
        return <<<EOT
この画像は旅行代理店からの合宿契約書です。以下の情報を抽出してJSON形式で返してください。

抽出する項目：
- lodging_fee_per_night: 1泊あたりの宿泊費（円、数値のみ）。「1泊3食」などの記載がある場合はその金額
- hot_spring_tax: 入湯税（円、数値のみ）。1泊あたりの金額
- court_fee_per_unit: テニスコート料金（円、数値のみ）。半日1面あたりの金額
- banquet_fee_per_person: 宴会場・大広間料金（円、数値のみ）。1人あたりの金額
- bus_fee_round_trip: バス料金往復合計（円、数値のみ）
- facility_name: 宿泊施設名
- start_date: 開始日（YYYY-MM-DD形式）
- end_date: 終了日（YYYY-MM-DD形式）

注意事項：
- 値が見つからない項目はnullとしてください
- 金額はカンマなしの数値で返してください（例: 8250）
- 日付は必ずYYYY-MM-DD形式で返してください
- JSONのみを返し、他の説明は不要です

出力形式：
{
  "type": "vision_ocr",
  "lodging_fee_per_night": 数値またはnull,
  "hot_spring_tax": 数値またはnull,
  "court_fee_per_unit": 数値またはnull,
  "banquet_fee_per_person": 数値またはnull,
  "bus_fee_round_trip": 数値またはnull,
  "facility_name": "文字列またはnull",
  "dates": {
    "start": "YYYY-MM-DD",
    "end": "YYYY-MM-DD"
  }
}
EOT;
    }

    /**
     * Vision APIのレスポンスをパース
     *
     * @param string $responseText
     * @return array
     * @throws Exception
     */
    private function parseVisionResponse(string $responseText): array
    {
        // JSONを抽出（マークダウンコードブロックに囲まれている場合も対応）
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $responseText, $matches)) {
            $jsonText = $matches[1];
        } elseif (preg_match('/\{[\s\S]*\}/', $responseText, $matches)) {
            $jsonText = $matches[0];
        } else {
            throw new Exception("AIからの応答をパースできませんでした");
        }

        $data = json_decode($jsonText, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSONパースエラー: " . json_last_error_msg());
        }

        // デフォルト値を設定
        $result = [
            'type' => 'vision_ocr',
            'lodging_fee_per_night' => $data['lodging_fee_per_night'] ?? null,
            'hot_spring_tax' => $data['hot_spring_tax'] ?? null,
            'court_fee_per_unit' => $data['court_fee_per_unit'] ?? null,
            'banquet_fee_per_person' => $data['banquet_fee_per_person'] ?? null,
            'bus_fee_round_trip' => $data['bus_fee_round_trip'] ?? null,
            'facility_name' => $data['facility_name'] ?? null,
            'dates' => $data['dates'] ?? null,
        ];

        return $result;
    }

    /**
     * 契約書の種類を判定
     *
     * @param string $text PDFのテキスト
     * @return string 契約書の種類
     */
    private function detectContractType(string $text): string
    {
        // コスモエージェンシー
        if (strpos($text, 'COSMO AGENCY') !== false || strpos($text, 'コスモエージェンシー') !== false) {
            if (strpos($text, '貸切バス') !== false || strpos($text, '往復貸切') !== false) {
                return 'cosmo_bus';
            }
            return 'cosmo_reservation';
        }

        // 毎日コムネット
        if (strpos($text, '御予約確認書') !== false) {
            return 'mainichi_reservation';
        }
        if (strpos($text, '貸切バス') !== false) {
            return 'mainichi_bus';
        }
        if (strpos($text, '旅行申込書兼確定書') !== false) {
            return 'mainichi_travel';
        }

        return 'unknown';
    }

    /**
     * コスモエージェンシー契約書（宿泊）をパース
     *
     * @param string $text PDFテキスト
     * @return array
     */
    private function parseCosmoReservation(string $text): array
    {
        $data = [
            'type' => 'cosmo_reservation',
            'lodging_fee_per_night' => null,
            'hot_spring_tax' => null,
            'court_fee_per_unit' => null,
            'banquet_fee_per_person' => null,
            'facility_name' => null,
            'dates' => null,
        ];

        // 施設名を抽出
        $data['facility_name'] = $this->extractFacilityName($text);

        // 宿泊費（1泊3食）を抽出 - パターン: ¥8,250 や 8,250
        // コスモの形式: 「1泊3食  ¥8,250」のようなパターン
        if (preg_match('/1泊3食.*?[¥￥]\s*([0-9,]+)/u', $text, $matches)) {
            $data['lodging_fee_per_night'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/宿泊料金.*?[¥￥]\s*([0-9,]+)/u', $text, $matches)) {
            $data['lodging_fee_per_night'] = (int)str_replace(',', '', $matches[1]);
        }

        // 入湯税を抽出
        if (preg_match('/入湯税.*?[¥￥]\s*([0-9,]+)/u', $text, $matches)) {
            $data['hot_spring_tax'] = (int)str_replace(',', '', $matches[1]);
        }

        // テニスコート料金を抽出
        // コスモの形式: 「テニスコート【オムニコート】...¥5,500 半日/1面」
        if (preg_match('/テニスコート.*?[¥￥]\s*([0-9,]+).*?半日/u', $text, $matches)) {
            $data['court_fee_per_unit'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/テニスコート.*?[¥￥]\s*([0-9,]+)/u', $text, $matches)) {
            $data['court_fee_per_unit'] = (int)str_replace(',', '', $matches[1]);
        }

        // 宴会場・大広間料金を抽出
        // コスモの形式: 「大広間...¥550 お1人様/1回」
        if (preg_match('/大広間.*?[¥￥]\s*([0-9,]+).*?1人/u', $text, $matches)) {
            $data['banquet_fee_per_person'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/宴会場.*?[¥￥]\s*([0-9,]+)/u', $text, $matches)) {
            $data['banquet_fee_per_person'] = (int)str_replace(',', '', $matches[1]);
        }

        // 日程を抽出
        // コスモの形式: 「2025年03月17日(月曜日)～2025年03月20日(木曜日)」
        if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日.*?[～〜~].*?(\d{4})年(\d{1,2})月(\d{1,2})日/u', $text, $matches)) {
            $data['dates'] = [
                'start' => sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]),
                'end' => sprintf('%04d-%02d-%02d', $matches[4], $matches[5], $matches[6]),
            ];
        } elseif (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日.*?[～〜~].*?(\d{1,2})月(\d{1,2})日/u', $text, $matches)) {
            $data['dates'] = [
                'start' => sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]),
                'end' => sprintf('%04d-%02d-%02d', $matches[1], $matches[4], $matches[5]),
            ];
        }

        return $data;
    }

    /**
     * コスモエージェンシー契約書（バス）をパース
     *
     * @param string $text PDFテキスト
     * @return array
     */
    private function parseCosmoBus(string $text): array
    {
        $data = [
            'type' => 'cosmo_bus',
            'bus_fee_round_trip' => null,
        ];

        // 往復バス料金を抽出
        // コスモの形式: 「往復貸切バス/大型バス正シート49席+補助席  ¥228,800」
        if (preg_match('/往復貸切バス.*?[¥￥]\s*([0-9,]+)/u', $text, $matches)) {
            $data['bus_fee_round_trip'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/合計金額.*?[¥￥]\s*([0-9,]+)/u', $text, $matches)) {
            $data['bus_fee_round_trip'] = (int)str_replace(',', '', $matches[1]);
        }

        return $data;
    }

    /**
     * 毎日コムネット御予約確認書をパース
     *
     * @param string $text PDFテキスト
     * @return array
     */
    private function parseMainichiReservation(string $text): array
    {
        $data = [
            'type' => 'reservation',
            'lodging_fee_per_night' => null,
            'hot_spring_tax' => null,
            'court_fee_per_unit' => null,
            'banquet_fee_per_person' => null,
            'facility_name' => null,
            'dates' => null,
        ];

        // 施設名を抽出
        if (preg_match('/【御予約確認書】.*?([^\s]+様)/', $text, $matches)) {
            $data['facility_name'] = $this->extractFacilityName($text);
        }

        // 宿泊費（1泊3食）を抽出
        // パターン: ¥8,250 や 8,250円
        if (preg_match('/1泊.*?3食.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['lodging_fee_per_night'] = (int)str_replace(',', '', $matches[1]);
        }

        // 入湯税を抽出
        if (preg_match('/入湯税.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['hot_spring_tax'] = (int)str_replace(',', '', $matches[1]);
        }

        // テニスコート料金を抽出（半日1面あたり）
        if (preg_match('/テニスコート.*?半日.*?1面.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['court_fee_per_unit'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/テニスコート.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['court_fee_per_unit'] = (int)str_replace(',', '', $matches[1]);
        }

        // 宴会場料金を抽出（1人あたり）
        if (preg_match('/宴会場|大広間.*?1人.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['banquet_fee_per_person'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/宴会場|大広間.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['banquet_fee_per_person'] = (int)str_replace(',', '', $matches[1]);
        }

        // 日程を抽出
        if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日.*?(\d{1,2})月(\d{1,2})日/u', $text, $matches)) {
            $data['dates'] = [
                'start' => sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]),
                'end' => sprintf('%04d-%02d-%02d', $matches[1], $matches[4], $matches[5]),
            ];
        }

        return $data;
    }

    /**
     * 毎日コムネット貸切バス契約書をパース
     *
     * @param string $text PDFテキスト
     * @return array
     */
    private function parseMainichiBus(string $text): array
    {
        $data = [
            'type' => 'bus',
            'bus_fee_round_trip' => null,
        ];

        // 往復料金を抽出
        if (preg_match('/往復.*?合計.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['bus_fee_round_trip'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/合計.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['bus_fee_round_trip'] = (int)str_replace(',', '', $matches[1]);
        }

        return $data;
    }

    /**
     * 毎日コムネット旅行申込書兼確定書をパース
     *
     * @param string $text PDFテキスト
     * @return array
     */
    private function parseMainichiTravel(string $text): array
    {
        $data = [
            'type' => 'travel',
            'total_amount' => null,
            'participant_count' => null,
            'lodging_fee' => null,
            'bus_fee' => null,
            'court_fee_per_unit' => null,
            'banquet_fee_per_person' => null,
            'facility_name' => null,
            'dates' => null,
        ];

        // 参加人数を抽出
        if (preg_match('/(\d+)\s*名/u', $text, $matches)) {
            $data['participant_count'] = (int)$matches[1];
        }

        // 合計金額を抽出
        if (preg_match('/合計.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['total_amount'] = (int)str_replace(',', '', $matches[1]);
        }

        // 宿泊費を抽出
        if (preg_match('/宿泊.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['lodging_fee'] = (int)str_replace(',', '', $matches[1]);
        }

        // バス代を抽出
        if (preg_match('/バス|交通.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['bus_fee'] = (int)str_replace(',', '', $matches[1]);
        }

        // テニスコート料金を抽出（クレー、半日1面あたり）
        if (preg_match('/テニスコート.*?クレー.*?半日.*?1面.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['court_fee_per_unit'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/テニスコート.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['court_fee_per_unit'] = (int)str_replace(',', '', $matches[1]);
        }

        // 宴会場料金を抽出
        if (preg_match('/宴会場|大広間.*?1人.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['banquet_fee_per_person'] = (int)str_replace(',', '', $matches[1]);
        }

        // 施設名を抽出
        $data['facility_name'] = $this->extractFacilityName($text);

        // 日程を抽出
        if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日.*?(\d{1,2})月(\d{1,2})日/u', $text, $matches)) {
            $data['dates'] = [
                'start' => sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]),
                'end' => sprintf('%04d-%02d-%02d', $matches[1], $matches[4], $matches[5]),
            ];
        }

        return $data;
    }

    /**
     * テキストから施設名を抽出
     *
     * @param string $text
     * @return string|null
     */
    private function extractFacilityName(string $text): ?string
    {
        // よくある施設名のパターン
        $patterns = [
            '/白子ホワイトパレス/u',
            '/ホワイトパレス/u',
            '/清風荘別館/u',
            '/山中湖清風荘/u',
            '/ホテル[^\s\n]+/u',
            '/旅館[^\s\n]+/u',
            '/[^\s\n]+ホテル/u',
            '/[^\s\n]+荘/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[0]);
            }
        }

        return null;
    }

    /**
     * 抽出データをバリデーション
     *
     * @param array $data
     * @return array エラーメッセージの配列（エラーがなければ空配列）
     */
    public function validate(array $data): array
    {
        $errors = [];

        $type = $data['type'] ?? '';

        if (in_array($type, ['reservation', 'cosmo_reservation', 'vision_ocr'])) {
            if (($data['lodging_fee_per_night'] ?? null) === null) {
                $errors[] = '宿泊費が抽出できませんでした';
            }
            if (($data['lodging_fee_per_night'] ?? 0) > 0 && $data['lodging_fee_per_night'] < 1000) {
                $errors[] = '宿泊費が異常に低い値です（¥' . number_format($data['lodging_fee_per_night']) . '）';
            }
        }

        if (in_array($type, ['bus', 'cosmo_bus'])) {
            if (($data['bus_fee_round_trip'] ?? null) === null) {
                $errors[] = 'バス料金が抽出できませんでした';
            }
        }

        return $errors;
    }
}
