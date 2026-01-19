<?php
/**
 * 出力サービス（PDF/Excel）
 * 注: 本番環境では TCPDF や PhpSpreadsheet をインストールする必要があります
 */
class ExportService
{
    /**
     * PDF出力
     */
    public function generatePdf(int $campId): void
    {
        $calculationService = new CalculationService();
        $result = $calculationService->calculate($campId);
        $partialSchedule = $calculationService->generatePartialParticipationSchedule($campId);

        // シンプルなHTMLベースのPDF出力
        // 本番環境では TCPDF または mPDF を使用
        $html = $this->generatePdfHtml($result, $partialSchedule);

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="camp_result_' . $campId . '.html"');

        echo $html;
        exit;
    }

    /**
     * Excel出力（CSV形式で代替）
     */
    public function generateExcel(int $campId): void
    {
        $calculationService = new CalculationService();
        $result = $calculationService->calculate($campId);
        $partialSchedule = $calculationService->generatePartialParticipationSchedule($campId);

        // CSV形式で出力
        // 本番環境では PhpSpreadsheet を使用
        $csv = $this->generateCsv($result, $partialSchedule);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="camp_result_' . $campId . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM for Excel UTF-8

        echo $csv;
        exit;
    }

    /**
     * フル参加かどうか判定
     */
    private function isFullParticipation(array $participant, int $totalDays): bool
    {
        $isFullJoin = ($participant['join_day'] == 1 && $participant['join_timing'] === 'outbound_bus');
        $isFullLeave = ($participant['leave_day'] == $totalDays && $participant['leave_timing'] === 'return_bus');
        return $isFullJoin && $isFullLeave;
    }

    /**
     * 参加者をフル参加と途中参加に分類
     */
    private function categorizeParticipants(array $participants, int $totalDays): array
    {
        $fullParticipants = [];
        $partialParticipants = [];

        foreach ($participants as $p) {
            if ($this->isFullParticipation($p, $totalDays)) {
                $fullParticipants[] = $p;
            } else {
                $partialParticipants[] = $p;
            }
        }

        return [
            'full' => $fullParticipants,
            'partial' => $partialParticipants,
        ];
    }

    /**
     * PDF用HTML生成
     */
    private function generatePdfHtml(array $result, array $partialSchedule): string
    {
        $camp = $result['camp'];
        $summary = $result['summary'];
        $participants = $result['participants'];
        $totalDays = $camp['nights'] + 1;

        // 参加者を分類
        $categorized = $this->categorizeParticipants($participants, $totalDays);
        $fullParticipants = $categorized['full'];
        $partialParticipants = $categorized['partial'];

        $html = '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($camp['name']) . ' - 精算表</title>
    <style>
        body { font-family: "Hiragino Sans", "Yu Gothic", sans-serif; font-size: 12px; }
        h1 { font-size: 18px; text-align: center; }
        h2 { font-size: 14px; margin-top: 30px; border-bottom: 2px solid #333; padding-bottom: 5px; }
        .summary { background: #f5f5f5; padding: 10px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 11px; }
        th { background: #e0e0e0; }
        .amount { text-align: right; }
        .total { font-weight: bold; background: #fff3cd; }
        .full-summary { background: #d4edda; }
        .partial-row { background: #fff; }
        .schedule-table th, .schedule-table td { padding: 3px 5px; text-align: center; font-size: 10px; }
        .schedule-table .name-col { text-align: left; white-space: nowrap; }
        .schedule-table .desc-col { text-align: left; font-size: 9px; max-width: 200px; }
        .attend { color: #198754; font-weight: bold; }
        .not-attend { color: #dc3545; }
        .page-break { page-break-before: always; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">印刷</button>
        <button onclick="window.close()">閉じる</button>
    </div>

    <h1>' . htmlspecialchars($camp['name']) . ' 精算表</h1>

    <div class="summary">
        <p>日程: ' . $camp['start_date'] . ' ～ ' . $camp['end_date'] . ' (' . $camp['nights'] . '泊' . ($camp['nights'] + 1) . '日)</p>
        <p>参加者数: ' . $summary['participant_count'] . '名（フル参加: ' . count($fullParticipants) . '名、途中参加/途中抜け: ' . count($partialParticipants) . '名）</p>
        <p>総額: ¥' . number_format($summary['total_amount']) . '</p>
        <p>平均: ¥' . number_format($summary['average_amount']) . '</p>
    </div>';

        // フル参加者セクション
        if (!empty($fullParticipants)) {
            $html .= '
    <h2>フル参加者（' . count($fullParticipants) . '名）</h2>
    <table>
        <thead>
            <tr>
                <th class="amount">負担額</th>
                <th>内訳</th>
                <th>対象者</th>
            </tr>
        </thead>
        <tbody>';

            // フル参加者の代表（最初の1人）の情報を使用
            $representative = $fullParticipants[0];
            $items = [];
            foreach ($representative['items'] as $item) {
                $items[] = $item['name'] . ': ¥' . number_format($item['amount']);
            }

            // フル参加者の名前リスト
            $names = array_map(function($p) { return htmlspecialchars($p['name']); }, $fullParticipants);

            $html .= '
            <tr class="full-summary">
                <td class="amount total">¥' . number_format($representative['total']) . '</td>
                <td><small>' . implode(', ', $items) . '</small></td>
                <td><small>' . implode('、', $names) . '</small></td>
            </tr>';

            $html .= '
        </tbody>
    </table>';
        }

        // 途中参加・途中抜けセクション
        if (!empty($partialParticipants)) {
            $html .= '
    <h2>途中参加・途中抜け（' . count($partialParticipants) . '名）</h2>
    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>参加期間</th>
                <th class="amount">負担額</th>
                <th>内訳</th>
            </tr>
        </thead>
        <tbody>';

            $joinTimingLabels = [
                'outbound_bus' => '往路バス', 'breakfast' => '朝食', 'morning' => '午前',
                'lunch' => '昼食', 'afternoon' => '午後', 'dinner' => '夕食', 'night' => '夜'
            ];
            $leaveTimingLabels = [
                'return_bus' => '復路バス', 'before_breakfast' => '朝食前', 'breakfast' => '朝食',
                'morning' => '午前', 'lunch' => '昼食', 'afternoon' => '午後', 'dinner' => '夕食', 'night' => '夜'
            ];

            foreach ($partialParticipants as $p) {
                $items = [];
                foreach ($p['items'] as $item) {
                    $items[] = $item['name'] . ': ¥' . number_format($item['amount']);
                }

                $joinLabel = ($joinTimingLabels[$p['join_timing']] ?? $p['join_timing']);
                $leaveLabel = ($leaveTimingLabels[$p['leave_timing']] ?? $p['leave_timing']);
                $period = $p['join_day'] . '日目' . $joinLabel . '～' . $p['leave_day'] . '日目' . $leaveLabel;

                $html .= '
            <tr class="partial-row">
                <td>' . htmlspecialchars($p['name']) . '</td>
                <td><small>' . $period . '</small></td>
                <td class="amount total">¥' . number_format($p['total']) . '</td>
                <td><small>' . implode(', ', $items) . '</small></td>
            </tr>';
            }

            $html .= '
        </tbody>
    </table>';
        }

        // 途中参加途中抜け一覧（スケジュール表）
        if (!empty($partialSchedule['rows'])) {
            $html .= '
    <div class="page-break"></div>
    <h2>途中参加・途中抜け スケジュール一覧</h2>
    <table class="schedule-table">
        <thead>
            <tr>
                <th rowspan="2" class="name-col">氏名</th>';

            // 日付ヘッダー
            foreach ($partialSchedule['headers'] as $dayHeader) {
                $colCount = count($dayHeader['columns']);
                $html .= '<th colspan="' . $colCount . '">' . $dayHeader['day'] . '日目</th>';
            }
            $html .= '</tr><tr>';

            // 項目ヘッダー
            foreach ($partialSchedule['headers'] as $dayHeader) {
                foreach ($dayHeader['columns'] as $col) {
                    $html .= '<th>' . htmlspecialchars($col['label']) . '</th>';
                }
            }
            $html .= '</tr></thead><tbody>';

            // 参加者行
            foreach ($partialSchedule['rows'] as $row) {
                $gradeLabel = $row['grade'] === 0 ? 'OB' : ($row['grade'] ? $row['grade'] . '年' : '');
                $genderIcon = $row['gender'] === 'male' ? '♂' : ($row['gender'] === 'female' ? '♀' : '');

                $html .= '<tr>
                <td class="name-col">' . htmlspecialchars($row['name']) . ' <small>(' . $gradeLabel . $genderIcon . ')</small></td>';

                foreach ($partialSchedule['headers'] as $dayHeader) {
                    foreach ($dayHeader['columns'] as $col) {
                        $attends = $row['schedule'][$col['key']] ?? false;
                        $html .= '<td class="' . ($attends ? 'attend' : 'not-attend') . '">' . ($attends ? '○' : '×') . '</td>';
                    }
                }
                $html .= '</tr>';
            }

            // 集計行
            $html .= '<tr style="background: #e9ecef; font-weight: bold;">
                <td>合計</td>';
            foreach ($partialSchedule['headers'] as $dayHeader) {
                foreach ($dayHeader['columns'] as $col) {
                    $count = $partialSchedule['totals'][$col['key']] ?? 0;
                    $html .= '<td>' . $count . '</td>';
                }
            }
            $html .= '</tr></tbody></table>';
        }

        $html .= '
    <p style="text-align: center; margin-top: 40px; font-size: 10px; color: #666;">
        出力日時: ' . date('Y/m/d H:i') . '
    </p>
</body>
</html>';

        return $html;
    }

    /**
     * CSV生成
     */
    private function generateCsv(array $result, array $partialSchedule): string
    {
        $camp = $result['camp'];
        $participants = $result['participants'];
        $totalDays = $camp['nights'] + 1;

        // 参加者を分類
        $categorized = $this->categorizeParticipants($participants, $totalDays);
        $fullParticipants = $categorized['full'];
        $partialParticipants = $categorized['partial'];

        $lines = [];

        // ヘッダー
        $lines[] = $this->csvLine(['合宿名', $camp['name']]);
        $lines[] = $this->csvLine(['日程', $camp['start_date'] . '～' . $camp['end_date']]);
        $lines[] = $this->csvLine(['参加者数', count($participants) . '名']);
        $lines[] = '';

        // 全カテゴリを収集
        $categories = [];
        foreach ($participants as $p) {
            foreach ($p['items'] as $item) {
                if (!in_array($item['category'], $categories)) {
                    $categories[] = $item['category'];
                }
            }
        }

        $catNames = [
            'lodging' => '宿泊費',
            'insurance' => '保険料',
            'meal_adjustment' => '食事調整',
            'bus' => 'バス代',
            'highway' => '高速代',
            'facility' => '施設利用料',
            'expense' => '雑費',
        ];

        // フル参加者セクション
        if (!empty($fullParticipants)) {
            $lines[] = $this->csvLine(['【フル参加者】', count($fullParticipants) . '名']);

            $headers = ['名前', '負担額'];
            foreach ($categories as $cat) {
                $headers[] = $catNames[$cat] ?? $cat;
            }
            $lines[] = $this->csvLine($headers);

            // フル参加者の代表（金額は同じなので最初の1人のみ詳細表示）
            $representative = $fullParticipants[0];
            $row = ['フル参加者（' . count($fullParticipants) . '名）', $representative['total']];
            foreach ($categories as $cat) {
                $catTotal = 0;
                foreach ($representative['items'] as $item) {
                    if ($item['category'] === $cat) {
                        $catTotal += $item['amount'];
                    }
                }
                $row[] = $catTotal;
            }
            $lines[] = $this->csvLine($row);

            // フル参加者の名前リスト
            $lines[] = $this->csvLine(['対象者', implode('、', array_map(function($p) { return $p['name']; }, $fullParticipants))]);
            $lines[] = '';
        }

        // 途中参加・途中抜けセクション
        if (!empty($partialParticipants)) {
            $lines[] = $this->csvLine(['【途中参加・途中抜け】', count($partialParticipants) . '名']);

            $headers = ['名前', '参加期間', '負担額'];
            foreach ($categories as $cat) {
                $headers[] = $catNames[$cat] ?? $cat;
            }
            $lines[] = $this->csvLine($headers);

            $joinTimingLabels = [
                'outbound_bus' => '往路バス', 'breakfast' => '朝食', 'morning' => '午前',
                'lunch' => '昼食', 'afternoon' => '午後', 'dinner' => '夕食', 'night' => '夜'
            ];
            $leaveTimingLabels = [
                'return_bus' => '復路バス', 'before_breakfast' => '朝食前', 'breakfast' => '朝食',
                'morning' => '午前', 'lunch' => '昼食', 'afternoon' => '午後', 'dinner' => '夕食', 'night' => '夜'
            ];

            foreach ($partialParticipants as $p) {
                $joinLabel = ($joinTimingLabels[$p['join_timing']] ?? $p['join_timing']);
                $leaveLabel = ($leaveTimingLabels[$p['leave_timing']] ?? $p['leave_timing']);
                $period = $p['join_day'] . '日目' . $joinLabel . '～' . $p['leave_day'] . '日目' . $leaveLabel;

                $row = [$p['name'], $period, $p['total']];
                foreach ($categories as $cat) {
                    $catTotal = 0;
                    foreach ($p['items'] as $item) {
                        if ($item['category'] === $cat) {
                            $catTotal += $item['amount'];
                        }
                    }
                    $row[] = $catTotal;
                }
                $lines[] = $this->csvLine($row);
            }
            $lines[] = '';
        }

        // 途中参加途中抜けスケジュール一覧
        if (!empty($partialSchedule['rows'])) {
            $lines[] = $this->csvLine(['【途中参加・途中抜け スケジュール一覧】']);

            // ヘッダー行1（日付）
            $dayHeaders = ['氏名'];
            foreach ($partialSchedule['headers'] as $dayHeader) {
                foreach ($dayHeader['columns'] as $col) {
                    $dayHeaders[] = $dayHeader['day'] . '日目';
                }
            }
            $lines[] = $this->csvLine($dayHeaders);

            // ヘッダー行2（項目）
            $colHeaders = [''];
            foreach ($partialSchedule['headers'] as $dayHeader) {
                foreach ($dayHeader['columns'] as $col) {
                    $colHeaders[] = $col['label'];
                }
            }
            $lines[] = $this->csvLine($colHeaders);

            // 参加者行
            foreach ($partialSchedule['rows'] as $row) {
                $gradeLabel = $row['grade'] === 0 ? 'OB' : ($row['grade'] ? $row['grade'] . '年' : '');
                $genderStr = $row['gender'] === 'male' ? '男' : ($row['gender'] === 'female' ? '女' : '');
                $nameWithGrade = $row['name'] . '(' . $gradeLabel . $genderStr . ')';

                $dataRow = [$nameWithGrade];
                foreach ($partialSchedule['headers'] as $dayHeader) {
                    foreach ($dayHeader['columns'] as $col) {
                        $attends = $row['schedule'][$col['key']] ?? false;
                        $dataRow[] = $attends ? '○' : '×';
                    }
                }
                $lines[] = $this->csvLine($dataRow);
            }

            // 集計行
            $totalRow = ['合計'];
            foreach ($partialSchedule['headers'] as $dayHeader) {
                foreach ($dayHeader['columns'] as $col) {
                    $totalRow[] = $partialSchedule['totals'][$col['key']] ?? 0;
                }
            }
            $lines[] = $this->csvLine($totalRow);
        }

        return implode("\n", $lines);
    }

    /**
     * CSV行を生成（カンマやクォートをエスケープ）
     */
    private function csvLine(array $values): string
    {
        $escaped = array_map(function($val) {
            $val = (string)$val;
            // カンマ、ダブルクォート、改行が含まれる場合はクォートで囲む
            if (strpos($val, ',') !== false || strpos($val, '"') !== false || strpos($val, "\n") !== false) {
                return '"' . str_replace('"', '""', $val) . '"';
            }
            return $val;
        }, $values);
        return implode(',', $escaped);
    }
}
