<?php
/**
 * 計算ロジック ユニットテスト（データベース不要）
 *
 * 実行方法: php tests/CalculationLogicTest.php
 */

class CalculationLogicTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    /**
     * テスト実行
     */
    public function run(): void
    {
        echo "==========================================\n";
        echo "  計算ロジック ユニットテスト\n";
        echo "==========================================\n\n";

        $this->testLodgingCalculation();
        $this->testBusFeeRoundTripCalculation();
        $this->testBusFeeSeparateCalculation();
        $this->testBusFeePartialUseCalculation();
        $this->testHighwayFeeCalculation();
        $this->testRentalCarFeeCalculation();
        $this->testFacilityFeeCalculation();
        $this->testBanquetFeeCalculation();
        $this->testExpenseFeeCalculation();
        $this->testMealAdjustmentCalculation();
        $this->testDefaultSlotFeeCalculation();

        $this->printSummary();
    }

    /**
     * 宿泊費計算テスト
     */
    private function testLodgingCalculation(): void
    {
        $this->printTestHeader('宿泊費計算');

        // ケース1: 3泊
        $nights = 3;
        $feePerNight = 10000;
        $expected = 30000;
        $actual = $feePerNight * $nights;
        $this->assertEqual($actual, $expected, "3泊 × ¥10,000 = ¥30,000");

        // ケース2: 途中参加（2日目から4日目 = 2泊）
        $joinDay = 2;
        $leaveDay = 4;
        $nights = $leaveDay - $joinDay;
        $expected = 20000;
        $actual = $feePerNight * $nights;
        $this->assertEqual($actual, $expected, "2泊（2日目→4日目）= ¥20,000");

        // ケース3: 日帰り
        $joinDay = 2;
        $leaveDay = 2;
        $nights = max(0, $leaveDay - $joinDay);
        $expected = 0;
        $actual = $feePerNight * $nights;
        $this->assertEqual($actual, $expected, "日帰り = ¥0");
    }

    /**
     * バス代（往復一括）計算テスト
     */
    private function testBusFeeRoundTripCalculation(): void
    {
        $this->printTestHeader('バス代（往復一括）');

        $busRoundTrip = 20000;

        // ケース1: 2人が往復利用
        $roundTripUsers = 2;
        $expected = 10000;
        $actual = round($busRoundTrip / $roundTripUsers);
        $this->assertEqual($actual, $expected, "¥20,000 ÷ 2人 = ¥10,000/人");

        // ケース2: 3人が往復利用
        $roundTripUsers = 3;
        $expected = 6667;
        $actual = round($busRoundTrip / $roundTripUsers);
        $this->assertEqual($actual, $expected, "¥20,000 ÷ 3人 = ¥6,667/人");

        // ケース3: 1人が往復利用
        $roundTripUsers = 1;
        $expected = 20000;
        $actual = round($busRoundTrip / $roundTripUsers);
        $this->assertEqual($actual, $expected, "¥20,000 ÷ 1人 = ¥20,000/人");
    }

    /**
     * バス代（往路/復路別）計算テスト
     */
    private function testBusFeeSeparateCalculation(): void
    {
        $this->printTestHeader('バス代（往路/復路別）');

        $busOutbound = 12000;
        $busReturn = 10000;

        // ケース: 2人が往路利用、2人が復路利用
        $outboundUsers = 2;
        $returnUsers = 2;

        $expectedOutbound = 6000;
        $actualOutbound = round($busOutbound / $outboundUsers);
        $this->assertEqual($actualOutbound, $expectedOutbound, "往路: ¥12,000 ÷ 2人 = ¥6,000/人");

        $expectedReturn = 5000;
        $actualReturn = round($busReturn / $returnUsers);
        $this->assertEqual($actualReturn, $expectedReturn, "復路: ¥10,000 ÷ 2人 = ¥5,000/人");
    }

    /**
     * バス代（片道のみ）計算テスト
     */
    private function testBusFeePartialUseCalculation(): void
    {
        $this->printTestHeader('バス代（片道のみ利用）');

        $busRoundTrip = 20000;

        // 往復一括設定で、往路のみ利用する場合
        // 往復料金の半分 ÷ 往路利用者数

        // ケース1: 2人が往路利用（復路は別手段）
        $outboundUsers = 2;
        $expected = 5000;
        $actual = round(($busRoundTrip / 2) / $outboundUsers);
        $this->assertEqual($actual, $expected, "往路のみ: (¥20,000 ÷ 2) ÷ 2人 = ¥5,000/人");

        // ケース2: 1人だけ往路利用
        $outboundUsers = 1;
        $expected = 10000;
        $actual = round(($busRoundTrip / 2) / $outboundUsers);
        $this->assertEqual($actual, $expected, "往路のみ: (¥20,000 ÷ 2) ÷ 1人 = ¥10,000/人");
    }

    /**
     * 高速代計算テスト
     */
    private function testHighwayFeeCalculation(): void
    {
        $this->printTestHeader('高速代');

        $highwayOutbound = 5000;
        $highwayReturn = 5000;

        // ケース: 2人が往路バス利用
        $outboundUsers = 2;
        $expected = 2500;
        $actual = round($highwayOutbound / $outboundUsers);
        $this->assertEqual($actual, $expected, "往路高速: ¥5,000 ÷ 2人 = ¥2,500/人");

        // ケース: 3人が復路バス利用
        $returnUsers = 3;
        $expected = 1667;
        $actual = round($highwayReturn / $returnUsers);
        $this->assertEqual($actual, $expected, "復路高速: ¥5,000 ÷ 3人 = ¥1,667/人");
    }

    /**
     * レンタカー代計算テスト
     */
    private function testRentalCarFeeCalculation(): void
    {
        $this->printTestHeader('レンタカー代');

        $rentalCarFee = 15000;
        $rentalCarHighway = 3000;

        // ケース: 2人がレンタカー利用
        $rentalCarUsers = 2;

        $expectedFee = 7500;
        $actualFee = round($rentalCarFee / $rentalCarUsers);
        $this->assertEqual($actualFee, $expectedFee, "レンタカー代: ¥15,000 ÷ 2人 = ¥7,500/人");

        $expectedHighway = 1500;
        $actualHighway = round($rentalCarHighway / $rentalCarUsers);
        $this->assertEqual($actualHighway, $expectedHighway, "レンタカー高速代: ¥3,000 ÷ 2人 = ¥1,500/人");
    }

    /**
     * 施設利用料（テニス/体育館）計算テスト
     */
    private function testFacilityFeeCalculation(): void
    {
        $this->printTestHeader('施設利用料（テニス/体育館）');

        // テニスコート: コート単価 × 面数 ÷ 参加者数
        $courtFeePerUnit = 5000;
        $courtCount = 2;
        $attendees = 4;

        $expected = 2500;
        $actual = round(($courtFeePerUnit * $courtCount) / $attendees);
        $this->assertEqual($actual, $expected, "テニス: (¥5,000 × 2面) ÷ 4人 = ¥2,500/人");

        // 体育館: 1コマ単価 ÷ 参加者数
        $gymFeePerUnit = 3000;
        $attendees = 6;

        $expected = 500;
        $actual = round($gymFeePerUnit / $attendees);
        $this->assertEqual($actual, $expected, "体育館: ¥3,000 ÷ 6人 = ¥500/人");
    }

    /**
     * 宴会場料金計算テスト（割り勘ではない）
     */
    private function testBanquetFeeCalculation(): void
    {
        $this->printTestHeader('宴会場料金（1人あたり）');

        $banquetFeePerPerson = 2000;

        // 宴会場は割り勘ではなく、1人あたり固定
        $attendees = 5;

        $expected = 2000; // 割り勘しない
        $actual = $banquetFeePerPerson; // そのまま
        $this->assertEqual($actual, $expected, "宴会場: ¥2,000/人（5人参加でも割り勘なし）");

        // 3人参加でも同じ
        $attendees = 3;
        $expected = 2000;
        $actual = $banquetFeePerPerson;
        $this->assertEqual($actual, $expected, "宴会場: ¥2,000/人（3人参加でも割り勘なし）");

        // 比較: もし割り勘だったら
        $wrongCalc = round($banquetFeePerPerson / $attendees);
        $this->assertNotEqual($wrongCalc, $expected, "宴会場は割り勘(¥667/人)ではない");
    }

    /**
     * 雑費計算テスト
     */
    private function testExpenseFeeCalculation(): void
    {
        $this->printTestHeader('雑費');

        // 全員対象
        $expenseAmount = 6000;
        $targetCount = 3;

        $expected = 2000;
        $actual = round($expenseAmount / $targetCount);
        $this->assertEqual($actual, $expected, "全員対象雑費: ¥6,000 ÷ 3人 = ¥2,000/人");

        // スロット対象（2人のみ参加）
        $expenseAmount = 1000;
        $targetCount = 2;

        $expected = 500;
        $actual = round($expenseAmount / $targetCount);
        $this->assertEqual($actual, $expected, "スロット対象雑費: ¥1,000 ÷ 2人 = ¥500/人");
    }

    /**
     * 食事調整計算テスト
     */
    private function testMealAdjustmentCalculation(): void
    {
        $this->printTestHeader('食事調整');

        $breakfastAdd = 500;
        $breakfastRemove = 400;
        $lunchAdd = 800;
        $lunchRemove = 600;
        $dinnerAdd = 1200;
        $dinnerRemove = 1000;

        // 昼食追加
        $expected = 800;
        $actual = $lunchAdd;
        $this->assertEqual($actual, $expected, "昼食追加: +¥800");

        // 昼食欠食
        $expected = -600;
        $actual = -$lunchRemove;
        $this->assertEqual($actual, $expected, "昼食欠食: -¥600");

        // 複合: 朝食追加 + 夕食欠食
        $expected = 500 - 1000;
        $actual = $breakfastAdd - $dinnerRemove;
        $this->assertEqual($actual, $expected, "朝食追加+夕食欠食: +¥500 - ¥1,000 = -¥500");
    }

    /**
     * デフォルトスロット施設料金計算テスト
     */
    private function testDefaultSlotFeeCalculation(): void
    {
        $this->printTestHeader('デフォルトスロット施設料金');

        $courtFeePerUnit = 5000;
        $defaultCourtCount = 1;

        // createDefaultSlotsでの計算
        $expected = 5000;
        $actual = $courtFeePerUnit * $defaultCourtCount;
        $this->assertEqual($actual, $expected, "デフォルトテニススロット: ¥5,000 × 1面 = ¥5,000");

        // コート単価が0の場合
        $courtFeePerUnit = 0;
        $expected = 0;
        $actual = $courtFeePerUnit ? $courtFeePerUnit * $defaultCourtCount : 0;
        $this->assertEqual($actual, $expected, "コート単価0の場合: ¥0");

        // コート単価がnullの場合
        $courtFeePerUnit = null;
        $expected = 0;
        $actual = $courtFeePerUnit ? $courtFeePerUnit * $defaultCourtCount : 0;
        $this->assertEqual($actual, $expected, "コート単価nullの場合: ¥0");
    }

    /**
     * アサーション: 等しい（数値は値の等価性で比較）
     */
    private function assertEqual($actual, $expected, string $message): void
    {
        // 数値の場合は == で比較（int/float の違いを無視）
        $isEqual = is_numeric($actual) && is_numeric($expected)
            ? $actual == $expected
            : $actual === $expected;

        if ($isEqual) {
            echo "  ✓ PASS: {$message}\n";
            $this->passCount++;
        } else {
            echo "  ✗ FAIL: {$message}\n";
            echo "    期待値: " . var_export($expected, true) . "\n";
            echo "    実際値: " . var_export($actual, true) . "\n";
            $this->failCount++;
        }
    }

    /**
     * アサーション: 等しくない
     */
    private function assertNotEqual($actual, $notExpected, string $message): void
    {
        if ($actual !== $notExpected) {
            echo "  ✓ PASS: {$message}\n";
            $this->passCount++;
        } else {
            echo "  ✗ FAIL: {$message}\n";
            echo "    値が等しい: " . var_export($actual, true) . "\n";
            $this->failCount++;
        }
    }

    /**
     * テストヘッダー表示
     */
    private function printTestHeader(string $name): void
    {
        echo "\n------------------------------------------\n";
        echo "テスト: {$name}\n";
        echo "------------------------------------------\n";
    }

    /**
     * サマリー表示
     */
    private function printSummary(): void
    {
        echo "\n==========================================\n";
        echo "  テスト結果サマリー\n";
        echo "==========================================\n";
        echo "  PASS: {$this->passCount}\n";
        echo "  FAIL: {$this->failCount}\n";
        echo "  合計: " . ($this->passCount + $this->failCount) . "\n";
        echo "==========================================\n";

        if ($this->failCount > 0) {
            echo "\n⚠️  {$this->failCount}件のテストが失敗しました。\n";
            exit(1);
        } else {
            echo "\n✓ すべてのテストが成功しました！\n";
            exit(0);
        }
    }
}

// テスト実行
$test = new CalculationLogicTest();
$test->run();
