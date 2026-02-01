<?php
/**
 * PDF解析サービス
 * 旅行代理店の契約書PDFから料金情報を抽出
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Smalot\PdfParser\Parser;

class PdfParserService
{
    private Parser $parser;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->parser = new Parser();
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
            default:
                throw new Exception("未対応の契約書形式です");
        }
    }

    /**
     * 契約書の種類を判定
     *
     * @param string $text PDFのテキスト
     * @return string 契約書の種類
     */
    private function detectContractType(string $text): string
    {
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
            'bus_fee_outbound' => null,
            'bus_fee_return' => null,
            'highway_fee_outbound' => null,
            'highway_fee_return' => null,
            'destination' => null,
        ];

        // 往復料金を抽出
        if (preg_match('/往復.*?合計.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['bus_fee_round_trip'] = (int)str_replace(',', '', $matches[1]);
        } elseif (preg_match('/合計.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['bus_fee_round_trip'] = (int)str_replace(',', '', $matches[1]);
        }

        // 往路料金を個別抽出
        if (preg_match('/往路.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['bus_fee_outbound'] = (int)str_replace(',', '', $matches[1]);
        }

        // 復路料金を個別抽出
        if (preg_match('/復路.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['bus_fee_return'] = (int)str_replace(',', '', $matches[1]);
        }

        // 高速代を抽出
        if (preg_match('/高速.*?往路.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['highway_fee_outbound'] = (int)str_replace(',', '', $matches[1]);
        }
        if (preg_match('/高速.*?復路.*?[¥￥]?\s*([0-9,]+)\s*円?/u', $text, $matches)) {
            $data['highway_fee_return'] = (int)str_replace(',', '', $matches[1]);
        }

        // 行き先を抽出
        if (preg_match('/行先|目的地.*?[:：]\s*([^\n]+)/u', $text, $matches)) {
            $data['destination'] = trim($matches[1]);
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
            '/ホワイトパレス/u',
            '/白子ホワイトパレス/u',
            '/清風荘別館/u',
            '/山中湖清風荘/u',
            '/ホテル[^\s]+/u',
            '/旅館[^\s]+/u',
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

        if ($data['type'] === 'reservation') {
            if ($data['lodging_fee_per_night'] === null) {
                $errors[] = '宿泊費が抽出できませんでした';
            }
            if ($data['lodging_fee_per_night'] !== null && $data['lodging_fee_per_night'] < 1000) {
                $errors[] = '宿泊費が異常に低い値です（¥' . number_format($data['lodging_fee_per_night']) . '）';
            }
        }

        if ($data['type'] === 'bus') {
            if ($data['bus_fee_round_trip'] === null && $data['bus_fee_outbound'] === null) {
                $errors[] = 'バス料金が抽出できませんでした';
            }
        }

        return $errors;
    }
}
