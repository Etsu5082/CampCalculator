<?php
/**
 * 計算サービス テストスイート
 *
 * 実行方法: php tests/CalculationServiceTest.php
 */

// 必要なファイルを読み込み
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/Camp.php';
require_once SRC_PATH . '/Models/Participant.php';
require_once SRC_PATH . '/Models/TimeSlot.php';
require_once SRC_PATH . '/Models/Expense.php';
require_once SRC_PATH . '/Services/CalculationService.php';

class CalculationServiceTest
{
    private Database $db;
    private int $testCampId;
    private array $testParticipantIds = [];
    private array $testResults = [];
    private int $passCount = 0;
    private int $failCount = 0;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * テスト実行
     */
    public function run(): void
    {
        echo "==========================================\n";
        echo "  計算サービス 包括的テストスイート\n";
        echo "==========================================\n\n";

        try {
            // テスト用合宿を作成
            $this->setupTestData();

            // 各テストを実行
            $this->testLodgingFee();
            $this->testInsuranceFee();
            $this->testMealAdjustment();
            $this->testBusFeeRoundTrip();
            $this->testBusFeeSeparate();
            $this->testBusFeePartialUse();
            $this->testHighwayFee();
            $this->testRentalCarFee();
            $this->testTennisCourtFee();
            $this->testGymFee();
            $this->testBanquetFee();
            $this->testExpenseAll();
            $this->testExpenseSlotTarget();
            $this->testPartialParticipation();
            $this->testDefaultSlotsWithFee();

            // 結果サマリー
            $this->printSummary();

        } catch (Exception $e) {
            echo "テスト実行エラー: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        } finally {
            // テストデータをクリーンアップ
            $this->cleanup();
        }
    }

    /**
     * テスト用データをセットアップ
     */
    private function setupTestData(): void
    {
        echo "テストデータをセットアップ中...\n";

        // テスト用合宿を作成
        $campModel = new Camp();
        $this->testCampId = $campModel->create([
            'name' => 'テスト合宿_' . date('YmdHis'),
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-04',
            'nights' => 3,
            'lodging_fee_per_night' => 10000,
            'breakfast_add_price' => 500,
            'breakfast_remove_price' => 400,
            'lunch_add_price' => 800,
            'lunch_remove_price' => 600,
            'dinner_add_price' => 1200,
            'dinner_remove_price' => 1000,
            'insurance_fee' => 300,
            'court_fee_per_unit' => 5000,
            'gym_fee_per_unit' => 3000,
            'banquet_fee_per_person' => 2000,
            'first_day_lunch_included' => 0,
            'bus_fee_round_trip' => 20000,
            'bus_fee_separate' => 0,
            'bus_fee_outbound' => null,
            'bus_fee_return' => null,
            'highway_fee_outbound' => 5000,
            'highway_fee_return' => 5000,
            'use_rental_car' => 1,
            'rental_car_fee' => 15000,
            'rental_car_highway_fee' => 3000,
            'rental_car_capacity' => 5,
        ]);

        echo "  合宿ID: {$this->testCampId}\n";

        // デフォルトのタイムスロットは createDefaultSlots で生成されている
        // 宴会スロットを追加
        $timeSlotModel = new TimeSlot();
        $timeSlotModel->create([
            'camp_id' => $this->testCampId,
            'day_number' => 1,
            'slot_type' => 'banquet',
            'activity_type' => 'banquet',
            'facility_fee' => 2000,
            'description' => '宴会場',
        ]);

        echo "  タイムスロット生成完了\n\n";
    }

    /**
     * テスト用参加者を作成
     */
    private function createTestParticipant(array $overrides = []): int
    {
        $defaults = [
            'camp_id' => $this->testCampId,
            'name' => 'テスト参加者_' . count($this->testParticipantIds),
            'grade' => 2,
            'gender' => 'male',
            'join_day' => 1,
            'join_timing' => 'morning',
            'leave_day' => 4,
            'leave_timing' => 'after_lunch',
            'use_outbound_bus' => 1,
            'use_return_bus' => 1,
            'use_rental_car' => 0,
        ];

        $data = array_merge($defaults, $overrides);
        $participantModel = new Participant();
        $id = $participantModel->create($data);
        $this->testParticipantIds[] = $id;
        return $id;
    }

    /**
     * テスト: 宿泊費計算
     */
    private function testLodgingFee(): void
    {
        $this->printTestHeader('宿泊費計算');

        // 全日程参加（3泊）
        $this->clearParticipants();
        $this->createTestParticipant(['name' => '全日程参加']);

        $result = $this->calculate();
        $lodging = $this->findItem($result['participants'][0], 'lodging');

        $expected = 10000 * 3; // 1泊1万円 × 3泊
        $this->assertEqual($lodging['amount'], $expected, "3泊の宿泊費: 期待値={$expected}, 実際={$lodging['amount']}");

        // 2泊参加
        $this->clearParticipants();
        $this->createTestParticipant(['name' => '2泊参加', 'join_day' => 2, 'leave_day' => 4]);

        $result = $this->calculate();
        $lodging = $this->findItem($result['participants'][0], 'lodging');

        $expected = 10000 * 2; // 2泊
        $this->assertEqual($lodging['amount'], $expected, "2泊の宿泊費: 期待値={$expected}, 実際={$lodging['amount']}");
    }

    /**
     * テスト: 保険料
     */
    private function testInsuranceFee(): void
    {
        $this->printTestHeader('保険料');

        $this->clearParticipants();
        $this->createTestParticipant(['name' => '保険料テスト']);

        $result = $this->calculate();
        $insurance = $this->findItem($result['participants'][0], 'insurance');

        $expected = 300;
        $this->assertEqual($insurance['amount'], $expected, "保険料: 期待値={$expected}, 実際={$insurance['amount']}");
    }

    /**
     * テスト: 食事調整
     */
    private function testMealAdjustment(): void
    {
        $this->printTestHeader('食事調整');

        // 途中参加（昼食から）
        $this->clearParticipants();
        $this->createTestParticipant([
            'name' => '昼食参加',
            'join_day' => 1,
            'join_timing' => 'lunch',
        ]);

        $result = $this->calculate();
        $meal = $this->findItem($result['participants'][0], 'meal_adjustment');

        // 昼食追加 +800円
        $expected = 800;
        $this->assertEqual($meal ? $meal['amount'] : 0, $expected, "昼食追加: 期待値={$expected}");

        // 早期離脱（朝食後）
        $this->clearParticipants();
        $this->createTestParticipant([
            'name' => '早期離脱',
            'leave_day' => 4,
            'leave_timing' => 'after_breakfast',
        ]);

        $result = $this->calculate();
        $meal = $this->findItem($result['participants'][0], 'meal_adjustment');

        // 昼食欠食 -600円
        $expected = -600;
        $this->assertEqual($meal ? $meal['amount'] : 0, $expected, "昼食欠食: 期待値={$expected}");
    }

    /**
     * テスト: バス代（往復一括）
     */
    private function testBusFeeRoundTrip(): void
    {
        $this->printTestHeader('バス代（往復一括）');

        $this->clearParticipants();
        // 2人とも往復利用
        $this->createTestParticipant(['name' => '往復利用者1']);
        $this->createTestParticipant(['name' => '往復利用者2']);

        $result = $this->calculate();
        $bus = $this->findItem($result['participants'][0], 'bus');

        // 20000円 ÷ 2人 = 10000円
        $expected = 10000;
        $this->assertEqual($bus ? $bus['amount'] : 0, $expected, "バス代往復（2人）: 期待値={$expected}");
    }

    /**
     * テスト: バス代（往路/復路別）
     */
    private function testBusFeeSeparate(): void
    {
        $this->printTestHeader('バス代（往路/復路別設定）');

        // 合宿設定を変更
        $this->updateCamp([
            'bus_fee_separate' => 1,
            'bus_fee_outbound' => 12000,
            'bus_fee_return' => 10000,
        ]);

        $this->clearParticipants();
        $this->createTestParticipant(['name' => '往復利用者1']);
        $this->createTestParticipant(['name' => '往復利用者2']);

        $result = $this->calculate();

        // 往路バス代を検索
        $outboundBus = null;
        $returnBus = null;
        foreach ($result['participants'][0]['items'] as $item) {
            if ($item['category'] === 'bus' && strpos($item['name'], '往路') !== false) {
                $outboundBus = $item;
            }
            if ($item['category'] === 'bus' && strpos($item['name'], '復路') !== false) {
                $returnBus = $item;
            }
        }

        // 往路: 12000 ÷ 2 = 6000
        $expected = 6000;
        $this->assertEqual($outboundBus ? $outboundBus['amount'] : 0, $expected, "往路バス代（2人）: 期待値={$expected}");

        // 復路: 10000 ÷ 2 = 5000
        $expected = 5000;
        $this->assertEqual($returnBus ? $returnBus['amount'] : 0, $expected, "復路バス代（2人）: 期待値={$expected}");

        // 設定を戻す
        $this->updateCamp([
            'bus_fee_separate' => 0,
            'bus_fee_outbound' => null,
            'bus_fee_return' => null,
        ]);
    }

    /**
     * テスト: バス代（片道のみ利用）
     */
    private function testBusFeePartialUse(): void
    {
        $this->printTestHeader('バス代（片道のみ利用）');

        $this->clearParticipants();
        // 1人は往復、1人は往路のみ
        $this->createTestParticipant(['name' => '往復利用', 'use_outbound_bus' => 1, 'use_return_bus' => 1]);
        $this->createTestParticipant(['name' => '往路のみ', 'use_outbound_bus' => 1, 'use_return_bus' => 0]);

        $result = $this->calculate();

        // 往復利用者のバス代
        $roundTripUser = $result['participants'][0];
        $bus = $this->findItem($roundTripUser, 'bus');
        // 往復料金20000 ÷ 往復利用者1人 = 20000
        $expected = 20000;
        $this->assertEqual($bus ? $bus['amount'] : 0, $expected, "往復利用者のバス代: 期待値={$expected}");

        // 往路のみ利用者のバス代
        $outboundOnlyUser = $result['participants'][1];
        $bus = $this->findItem($outboundOnlyUser, 'bus');
        // 往復料金の半分10000 ÷ 往路利用者2人 = 5000
        $expected = 5000;
        $this->assertEqual($bus ? $bus['amount'] : 0, $expected, "往路のみ利用者のバス代: 期待値={$expected}");
    }

    /**
     * テスト: 高速代
     */
    private function testHighwayFee(): void
    {
        $this->printTestHeader('高速代');

        $this->clearParticipants();
        $this->createTestParticipant(['name' => '高速代テスト1']);
        $this->createTestParticipant(['name' => '高速代テスト2']);

        $result = $this->calculate();

        $outboundHighway = null;
        $returnHighway = null;
        foreach ($result['participants'][0]['items'] as $item) {
            if ($item['category'] === 'highway' && strpos($item['name'], '往路') !== false) {
                $outboundHighway = $item;
            }
            if ($item['category'] === 'highway' && strpos($item['name'], '復路') !== false) {
                $returnHighway = $item;
            }
        }

        // 往路高速: 5000 ÷ 2 = 2500
        $expected = 2500;
        $this->assertEqual($outboundHighway ? $outboundHighway['amount'] : 0, $expected, "往路高速代（2人）: 期待値={$expected}");

        // 復路高速: 5000 ÷ 2 = 2500
        $expected = 2500;
        $this->assertEqual($returnHighway ? $returnHighway['amount'] : 0, $expected, "復路高速代（2人）: 期待値={$expected}");
    }

    /**
     * テスト: レンタカー代
     */
    private function testRentalCarFee(): void
    {
        $this->printTestHeader('レンタカー代');

        $this->clearParticipants();
        // 2人がレンタカー利用
        $this->createTestParticipant(['name' => 'レンタカー1', 'use_rental_car' => 1, 'use_outbound_bus' => 0, 'use_return_bus' => 0]);
        $this->createTestParticipant(['name' => 'レンタカー2', 'use_rental_car' => 1, 'use_outbound_bus' => 0, 'use_return_bus' => 0]);
        $this->createTestParticipant(['name' => 'バス利用', 'use_rental_car' => 0]);

        $result = $this->calculate();

        // レンタカー利用者1のレンタカー代
        $rental = $this->findItem($result['participants'][0], 'rental_car');
        // 15000 ÷ 2 = 7500
        $expected = 7500;
        $this->assertEqual($rental ? $rental['amount'] : 0, $expected, "レンタカー代（2人）: 期待値={$expected}");

        // レンタカー高速代
        $rentalHighway = $this->findItem($result['participants'][0], 'rental_car_highway');
        // 3000 ÷ 2 = 1500
        $expected = 1500;
        $this->assertEqual($rentalHighway ? $rentalHighway['amount'] : 0, $expected, "レンタカー高速代（2人）: 期待値={$expected}");

        // バス利用者はレンタカー代なし
        $rental = $this->findItem($result['participants'][2], 'rental_car');
        $this->assertEqual($rental, null, "バス利用者にはレンタカー代なし");
    }

    /**
     * テスト: テニスコート料金
     */
    private function testTennisCourtFee(): void
    {
        $this->printTestHeader('テニスコート料金');

        $this->clearParticipants();
        $this->createTestParticipant(['name' => 'テニス参加1']);
        $this->createTestParticipant(['name' => 'テニス参加2']);

        $result = $this->calculate();

        // 施設利用料を確認
        $facilityItems = [];
        foreach ($result['participants'][0]['items'] as $item) {
            if ($item['category'] === 'facility') {
                $facilityItems[] = $item;
            }
        }

        // デフォルトスロットではcourt_fee_per_unit(5000) × court_count(1) = 5000
        // 2人参加なら 5000 ÷ 2 = 2500
        $tennisSlotFound = false;
        foreach ($facilityItems as $item) {
            if (strpos($item['name'], '午前') !== false || strpos($item['name'], '午後') !== false) {
                $expected = 2500; // 5000 ÷ 2
                $this->assertEqual($item['amount'], $expected, "{$item['name']} コート代: 期待値={$expected}, 実際={$item['amount']}");
                $tennisSlotFound = true;
                break;
            }
        }

        if (!$tennisSlotFound) {
            $this->assertEqual(false, true, "テニスコートスロットが見つからない（facility_fee=0の可能性）");
        }
    }

    /**
     * テスト: 体育館料金
     */
    private function testGymFee(): void
    {
        $this->printTestHeader('体育館料金');

        // 体育館スロットを追加
        $timeSlotModel = new TimeSlot();
        $timeSlotModel->create([
            'camp_id' => $this->testCampId,
            'day_number' => 2,
            'slot_type' => 'afternoon',
            'activity_type' => 'gym',
            'facility_fee' => 3000, // gym_fee_per_unit
            'description' => '体育館',
        ]);

        $this->clearParticipants();
        $this->createTestParticipant(['name' => '体育館参加1']);
        $this->createTestParticipant(['name' => '体育館参加2']);

        $result = $this->calculate();

        // 体育館の施設利用料を確認
        $gymFound = false;
        foreach ($result['participants'][0]['items'] as $item) {
            if ($item['category'] === 'facility' && strpos($item['name'], '2日目午後') !== false) {
                // 3000 ÷ 2 = 1500
                $expected = 1500;
                $this->assertEqual($item['amount'], $expected, "体育館代（2人）: 期待値={$expected}, 実際={$item['amount']}");
                $gymFound = true;
                break;
            }
        }

        if (!$gymFound) {
            $this->assertEqual(false, true, "体育館スロットが見つからない");
        }
    }

    /**
     * テスト: 宴会場料金（1人あたり）
     */
    private function testBanquetFee(): void
    {
        $this->printTestHeader('宴会場料金（1人あたり）');

        $this->clearParticipants();
        $this->createTestParticipant(['name' => '宴会参加1']);
        $this->createTestParticipant(['name' => '宴会参加2']);
        $this->createTestParticipant(['name' => '宴会参加3']);

        $result = $this->calculate();

        // 宴会場の施設利用料を確認
        $banquetFound = false;
        foreach ($result['participants'][0]['items'] as $item) {
            if ($item['category'] === 'facility' && strpos($item['name'], '宴会') !== false) {
                // 宴会場は1人あたり2000円（割り勘ではない）
                $expected = 2000;
                $this->assertEqual($item['amount'], $expected, "宴会場代（1人あたり）: 期待値={$expected}, 実際={$item['amount']}");
                $banquetFound = true;
                break;
            }
        }

        if (!$banquetFound) {
            $this->assertEqual(false, true, "宴会場スロットが見つからない");
        }
    }

    /**
     * テスト: 雑費（全員対象）
     */
    private function testExpenseAll(): void
    {
        $this->printTestHeader('雑費（全員対象）');

        // 雑費を追加
        $expenseModel = new Expense();
        $expenseModel->create([
            'camp_id' => $this->testCampId,
            'name' => 'ボール代',
            'amount' => 6000,
            'target_type' => 'all',
            'target_day' => null,
            'target_slot' => null,
        ]);

        $this->clearParticipants();
        $this->createTestParticipant(['name' => '雑費テスト1']);
        $this->createTestParticipant(['name' => '雑費テスト2']);
        $this->createTestParticipant(['name' => '雑費テスト3']);

        $result = $this->calculate();

        $expense = $this->findItem($result['participants'][0], 'expense');
        // 6000 ÷ 3 = 2000
        $expected = 2000;
        $this->assertEqual($expense ? $expense['amount'] : 0, $expected, "雑費（3人）: 期待値={$expected}");
    }

    /**
     * テスト: 雑費（スロット対象）
     */
    private function testExpenseSlotTarget(): void
    {
        $this->printTestHeader('雑費（スロット対象）');

        // 特定スロット対象の雑費を追加
        $expenseModel = new Expense();
        $expenseModel->create([
            'camp_id' => $this->testCampId,
            'name' => '午前ドリンク代',
            'amount' => 1000,
            'target_type' => 'slot',
            'target_day' => 2,
            'target_slot' => 'morning',
        ]);

        $this->clearParticipants();
        // 全日程参加者（2日目午前参加）
        $this->createTestParticipant(['name' => '全日程参加']);
        // 2日目から参加（2日目午前参加）
        $this->createTestParticipant(['name' => '2日目参加', 'join_day' => 2]);

        $result = $this->calculate();

        // 両者とも2日目午前に参加しているので対象
        $expense1 = null;
        foreach ($result['participants'][0]['items'] as $item) {
            if ($item['category'] === 'expense' && strpos($item['name'], 'ドリンク') !== false) {
                $expense1 = $item;
            }
        }
        // 1000 ÷ 2 = 500
        $expected = 500;
        $this->assertEqual($expense1 ? $expense1['amount'] : 0, $expected, "スロット対象雑費（2人対象）: 期待値={$expected}");
    }

    /**
     * テスト: 部分参加
     */
    private function testPartialParticipation(): void
    {
        $this->printTestHeader('部分参加');

        $this->clearParticipants();
        // 2日目から参加、3日目終了
        $this->createTestParticipant([
            'name' => '部分参加者',
            'join_day' => 2,
            'join_timing' => 'morning',
            'leave_day' => 3,
            'leave_timing' => 'after_lunch',
            'use_outbound_bus' => 0,
            'use_return_bus' => 0,
        ]);

        $result = $this->calculate();
        $p = $result['participants'][0];

        // 宿泊費: 1泊（2日目→3日目）
        $lodging = $this->findItem($p, 'lodging');
        $expected = 10000;
        $this->assertEqual($lodging ? $lodging['amount'] : 0, $expected, "部分参加の宿泊費（1泊）: 期待値={$expected}");

        // バス代なし
        $bus = $this->findItem($p, 'bus');
        $this->assertEqual($bus, null, "バス不使用者にはバス代なし");

        // 高速代なし
        $highway = $this->findItem($p, 'highway');
        $this->assertEqual($highway, null, "バス不使用者には高速代なし");
    }

    /**
     * テスト: createDefaultSlotsで施設料金が設定されるか
     */
    private function testDefaultSlotsWithFee(): void
    {
        $this->printTestHeader('デフォルトスロット施設料金');

        // 新しい合宿を作成（コート単価5000円）
        $campModel = new Camp();
        $newCampId = $campModel->create([
            'name' => 'スロットテスト合宿_' . date('YmdHis'),
            'start_date' => '2025-04-01',
            'end_date' => '2025-04-03',
            'nights' => 2,
            'lodging_fee_per_night' => 8000,
            'court_fee_per_unit' => 4000,
            'gym_fee_per_unit' => 2500,
            'insurance_fee' => 200,
        ]);

        // タイムスロットを確認
        $timeSlotModel = new TimeSlot();
        $slots = $timeSlotModel->getByCampId($newCampId);

        $tennisSlotWithFee = false;
        foreach ($slots as $slot) {
            if ($slot['activity_type'] === 'tennis' && $slot['facility_fee'] > 0) {
                $tennisSlotWithFee = true;
                // facility_fee = court_fee_per_unit(4000) × court_count(1) = 4000
                $expected = 4000;
                $this->assertEqual($slot['facility_fee'], $expected, "デフォルトスロットのテニスコート料金: 期待値={$expected}, 実際={$slot['facility_fee']}");
                break;
            }
        }

        if (!$tennisSlotWithFee) {
            $this->assertEqual(false, true, "デフォルトスロットにfacility_feeが設定されていない");
        }

        // クリーンアップ
        $campModel->delete($newCampId);
    }

    /**
     * 計算実行
     */
    private function calculate(): array
    {
        $service = new CalculationService();
        return $service->calculate($this->testCampId);
    }

    /**
     * アイテムを検索
     */
    private function findItem(array $participant, string $category): ?array
    {
        foreach ($participant['items'] as $item) {
            if ($item['category'] === $category) {
                return $item;
            }
        }
        return null;
    }

    /**
     * 参加者をクリア
     */
    private function clearParticipants(): void
    {
        foreach ($this->testParticipantIds as $id) {
            $this->db->execute("DELETE FROM meal_adjustments WHERE participant_id = ?", [$id]);
            $this->db->execute("DELETE FROM participant_slots WHERE participant_id = ?", [$id]);
            $this->db->execute("DELETE FROM participants WHERE id = ?", [$id]);
        }
        $this->testParticipantIds = [];
    }

    /**
     * 合宿設定を更新
     */
    private function updateCamp(array $data): void
    {
        $campModel = new Camp();
        $campModel->update($this->testCampId, $data);
    }

    /**
     * アサーション
     */
    private function assertEqual($actual, $expected, string $message): void
    {
        if ($actual === $expected) {
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
        } else {
            echo "\n✓ すべてのテストが成功しました！\n";
        }
    }

    /**
     * クリーンアップ
     */
    private function cleanup(): void
    {
        echo "\nテストデータをクリーンアップ中...\n";

        // 参加者をクリア
        $this->clearParticipants();

        // 雑費をクリア
        $this->db->execute("DELETE FROM expenses WHERE camp_id = ?", [$this->testCampId]);

        // タイムスロットをクリア
        $this->db->execute("DELETE FROM time_slots WHERE camp_id = ?", [$this->testCampId]);

        // 合宿をクリア
        $this->db->execute("DELETE FROM camps WHERE id = ?", [$this->testCampId]);

        echo "クリーンアップ完了\n";
    }
}

// テスト実行
$test = new CalculationServiceTest();
$test->run();
