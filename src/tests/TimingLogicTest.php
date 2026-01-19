<?php
/**
 * 参加タイミング計算ロジック テスト
 *
 * 実行方法: php tests/TimingLogicTest.php
 */

class TimingLogicTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    /**
     * テスト実行
     */
    public function run(): void
    {
        echo "==========================================\n";
        echo "  参加タイミング計算ロジック テスト\n";
        echo "==========================================\n\n";

        $this->testNightsCalculation();
        $this->testMealParticipation();
        $this->testEventParticipation();
        $this->testAutoMealAdjustment();

        $this->printSummary();
    }

    /**
     * 宿泊数計算テスト
     */
    private function testNightsCalculation(): void
    {
        $this->printTestHeader('宿泊数計算');

        // ケース1: フル参加（1日目〜最終日）
        $nights = $this->calculateNights(1, 4, 'outbound_bus', 'return_bus');
        $this->assertEqual($nights, 3, "フル参加（1日目〜4日目）: 3泊");

        // ケース2: 途中参加（2日目朝食〜4日目）
        $nights = $this->calculateNights(2, 4, 'breakfast', 'return_bus');
        $this->assertEqual($nights, 2, "2日目朝食〜4日目: 2泊");

        // ケース3: 途中離脱（1日目〜2日目夕食まで）
        $nights = $this->calculateNights(1, 2, 'outbound_bus', 'dinner');
        $this->assertEqual($nights, 1, "1日目〜2日目夕食まで: 1泊");

        // ケース4: 途中離脱（1日目〜2日目夜まで = 夜イベントに参加するが2日目は泊まらない）
        $nights = $this->calculateNights(1, 2, 'outbound_bus', 'night');
        $this->assertEqual($nights, 1, "1日目〜2日目夜まで: 1泊（2日目夜イベント参加後帰宅）");

        // ケース5: 日帰り（1日目の昼食まで）
        $nights = $this->calculateNights(1, 1, 'outbound_bus', 'lunch');
        $this->assertEqual($nights, 0, "1日目昼食まで（日帰り）: 0泊");

        // ケース6: 朝食前離脱
        $nights = $this->calculateNights(1, 2, 'outbound_bus', 'before_breakfast');
        $this->assertEqual($nights, 1, "1日目〜2日目朝食前まで: 1泊");
    }

    /**
     * 食事参加判定テスト
     */
    private function testMealParticipation(): void
    {
        $this->printTestHeader('食事参加判定');

        // 参加開始タイミングのテスト
        // 「朝食から」参加 → 朝食を食べる
        $eats = $this->doesEatMeal(1, 1, 'breakfast', 'breakfast', 'return_bus');
        $this->assertEqual($eats, true, "朝食から参加 → 朝食を食べる");

        // 「午前イベントから」参加 → 朝食を食べない
        $eats = $this->doesEatMeal(1, 1, 'breakfast', 'morning', 'return_bus');
        $this->assertEqual($eats, false, "午前イベントから参加 → 朝食を食べない");

        // 「昼食から」参加 → 朝食・昼食を食べる（昼食からなので昼食は食べる）
        $eats = $this->doesEatMeal(1, 1, 'lunch', 'lunch', 'return_bus');
        $this->assertEqual($eats, true, "昼食から参加 → 昼食を食べる");

        // 「午後イベントから」参加 → 昼食を食べない
        $eats = $this->doesEatMeal(1, 1, 'lunch', 'afternoon', 'return_bus');
        $this->assertEqual($eats, false, "午後イベントから参加 → 昼食を食べない");

        // 離脱タイミングのテスト
        // 「朝食前まで」離脱 → 朝食を食べない
        $eats = $this->doesEatMeal(2, 2, 'breakfast', 'outbound_bus', 'before_breakfast');
        $this->assertEqual($eats, false, "朝食前まで離脱 → 朝食を食べない");

        // 「朝食まで」離脱 → 朝食を食べる
        $eats = $this->doesEatMeal(2, 2, 'breakfast', 'outbound_bus', 'breakfast');
        $this->assertEqual($eats, true, "朝食まで離脱 → 朝食を食べる");

        // 「午前イベントまで」離脱 → 昼食を食べない
        $eats = $this->doesEatMeal(2, 2, 'lunch', 'outbound_bus', 'morning');
        $this->assertEqual($eats, false, "午前イベントまで離脱 → 昼食を食べない");

        // 「昼食まで」離脱 → 昼食を食べる
        $eats = $this->doesEatMeal(2, 2, 'lunch', 'outbound_bus', 'lunch');
        $this->assertEqual($eats, true, "昼食まで離脱 → 昼食を食べる");

        // 「午後イベントまで」離脱 → 夕食を食べない
        $eats = $this->doesEatMeal(2, 2, 'dinner', 'outbound_bus', 'afternoon');
        $this->assertEqual($eats, false, "午後イベントまで離脱 → 夕食を食べない");

        // 「夕食まで」離脱 → 夕食を食べる
        $eats = $this->doesEatMeal(2, 2, 'dinner', 'outbound_bus', 'dinner');
        $this->assertEqual($eats, true, "夕食まで離脱 → 夕食を食べる");

        // 参加期間外
        $eats = $this->doesEatMeal(1, 4, 'breakfast', 'outbound_bus', 'return_bus', 2);
        $this->assertEqual($eats, false, "参加期間外（1日目の食事、2日目参加）→ 食べない");
    }

    /**
     * イベント参加判定テスト
     */
    private function testEventParticipation(): void
    {
        $this->printTestHeader('イベント参加判定');

        // 「午前イベントから」参加 → 午前イベントに参加
        $attends = $this->doesAttendEvent(1, 1, 'morning', 'morning', 'return_bus');
        $this->assertEqual($attends, true, "午前イベントから参加 → 午前イベントに参加");

        // 「昼食から」参加 → 午前イベントに参加しない
        $attends = $this->doesAttendEvent(1, 1, 'morning', 'lunch', 'return_bus');
        $this->assertEqual($attends, false, "昼食から参加 → 午前イベントに参加しない");

        // 「午後イベントから」参加 → 午後イベントに参加
        $attends = $this->doesAttendEvent(1, 1, 'afternoon', 'afternoon', 'return_bus');
        $this->assertEqual($attends, true, "午後イベントから参加 → 午後イベントに参加");

        // 「午前イベントまで」離脱 → 午前イベントに参加、午後イベントに参加しない
        $attends = $this->doesAttendEvent(2, 2, 'morning', 'outbound_bus', 'morning');
        $this->assertEqual($attends, true, "午前イベントまで離脱 → 午前イベントに参加");

        $attends = $this->doesAttendEvent(2, 2, 'afternoon', 'outbound_bus', 'morning');
        $this->assertEqual($attends, false, "午前イベントまで離脱 → 午後イベントに参加しない");

        // 「午後イベントまで」離脱 → 午後イベントに参加、宴会に参加しない
        $attends = $this->doesAttendEvent(2, 2, 'afternoon', 'outbound_bus', 'afternoon');
        $this->assertEqual($attends, true, "午後イベントまで離脱 → 午後イベントに参加");

        $attends = $this->doesAttendEvent(2, 2, 'banquet', 'outbound_bus', 'afternoon');
        $this->assertEqual($attends, false, "午後イベントまで離脱 → 宴会に参加しない");

        // 「夜まで」離脱 → 宴会に参加
        $attends = $this->doesAttendEvent(2, 2, 'banquet', 'outbound_bus', 'night');
        $this->assertEqual($attends, true, "夜まで離脱 → 宴会に参加");

        // 参加期間外
        $attends = $this->doesAttendEvent(1, 4, 'morning', 'outbound_bus', 'return_bus', 2);
        $this->assertEqual($attends, false, "参加期間外（1日目のイベント、2日目参加）→ 参加しない");
    }

    /**
     * 自動食事調整テスト（宿泊ベース）
     *
     * 宿泊と食事の対応関係:
     * - N泊目に含まれる食事: N日目夕食、(N+1)日目朝食、(N+1)日目昼食
     * - 宿泊している泊の食事を食べなかったら → 欠食（減算）
     * - 宿泊していない泊の食事を食べたら → 追加（加算）
     * - 1日目昼食は宿泊に含まれないため、自動調整の対象外
     */
    private function testAutoMealAdjustment(): void
    {
        $this->printTestHeader('自動食事調整（宿泊ベース）');

        // 3泊4日合宿の設定
        $camp = [
            'nights' => 3,
            'breakfast_add_price' => 600,
            'breakfast_remove_price' => 500,
            'lunch_add_price' => 900,
            'lunch_remove_price' => 800,
            'dinner_add_price' => 1200,
            'dinner_remove_price' => 1000,
        ];

        // ケース1: フル参加（調整なし）
        $participant1 = [
            'join_day' => 1,
            'leave_day' => 4,
            'join_timing' => 'outbound_bus',
            'leave_timing' => 'return_bus',
        ];
        $adjustment1 = $this->calculateAutoMealAdjustment($camp, $participant1);
        $this->assertEqual($adjustment1['total'], 0, "フル参加: 調整なし = 0円");

        // ケース2: 2日目昼食から参加、3日目昼食で帰る（2泊目のみ宿泊）
        // 宿泊: 2泊目のみ（2日目夜に宿泊）
        // 1泊目（不宿泊）の食事: 1日目夕食×、2日目朝食×、2日目昼食○ → 2日目昼食追加
        // 2泊目（宿泊）の食事: 2日目夕食○、3日目朝食○、3日目昼食○ → 調整なし
        // 3泊目（不宿泊）の食事: 3日目夕食×、4日目朝食×、4日目昼食× → 調整なし
        $participant2 = [
            'join_day' => 2,
            'leave_day' => 3,
            'join_timing' => 'lunch',
            'leave_timing' => 'lunch',
        ];
        $adjustment2 = $this->calculateAutoMealAdjustment($camp, $participant2);
        // 2日目昼食追加 +900
        $expected2 = 900;
        $this->assertEqual($adjustment2['total'], $expected2, "2日目昼食〜3日目昼食: 2日目昼食追加 = {$expected2}円");

        // ケース3: 2日目昼食から参加、3日目夕食で帰る（2泊目のみ宿泊）
        // 1泊目（不宿泊）: 2日目昼食○ → 追加
        // 2泊目（宿泊）: 全て○ → 調整なし
        // 3泊目（不宿泊）: 3日目夕食○ → 追加
        $participant3 = [
            'join_day' => 2,
            'leave_day' => 3,
            'join_timing' => 'lunch',
            'leave_timing' => 'dinner',
        ];
        $adjustment3 = $this->calculateAutoMealAdjustment($camp, $participant3);
        // 2日目昼食追加 +900、3日目夕食追加 +1200
        $expected3 = 900 + 1200;
        $this->assertEqual($adjustment3['total'], $expected3, "2日目昼食〜3日目夕食: 2日目昼食追加 + 3日目夕食追加 = {$expected3}円");

        // ケース4: 1日目〜2日目朝食前に帰る（1泊のみ）
        // 1泊目（宿泊）: 1日目夕食○、2日目朝食×、2日目昼食× → 朝食欠食、昼食欠食
        $participant4 = [
            'join_day' => 1,
            'leave_day' => 2,
            'join_timing' => 'outbound_bus',
            'leave_timing' => 'before_breakfast',
        ];
        $adjustment4 = $this->calculateAutoMealAdjustment($camp, $participant4);
        // 2日目朝食欠食 -500、2日目昼食欠食 -800
        $expected4 = -500 - 800;
        $this->assertEqual($adjustment4['total'], $expected4, "1日目〜2日目朝食前: 2日目朝食・昼食欠食 = {$expected4}円");

        // ケース5: 2日目午前イベントから参加（朝食を食べない）、最終日まで
        // 1泊目（不宿泊）: 1日目夕食×、2日目朝食×、2日目昼食○ → 2日目昼食追加
        // 2泊目（宿泊）: 全て○
        // 3泊目（宿泊）: 全て○
        $participant5 = [
            'join_day' => 2,
            'leave_day' => 4,
            'join_timing' => 'morning',
            'leave_timing' => 'return_bus',
        ];
        $adjustment5 = $this->calculateAutoMealAdjustment($camp, $participant5);
        // 2日目昼食追加 +900
        $expected5 = 900;
        $this->assertEqual($adjustment5['total'], $expected5, "2日目午前〜最終日: 2日目昼食追加 = {$expected5}円");

        // ケース6: 最終日午前イベントまで参加（昼食を食べない）
        // 3泊目（宿泊）: 3日目夕食○、4日目朝食○、4日目昼食× → 昼食欠食
        $participant6 = [
            'join_day' => 1,
            'leave_day' => 4,
            'join_timing' => 'outbound_bus',
            'leave_timing' => 'morning',
        ];
        $adjustment6 = $this->calculateAutoMealAdjustment($camp, $participant6);
        // 4日目昼食欠食 -800
        $expected6 = -800;
        $this->assertEqual($adjustment6['total'], $expected6, "最終日午前まで: 4日目昼食欠食 = {$expected6}円");

        // ケース7: 1日目夕食から参加（1日目昼食は宿泊に含まれないので調整なし）
        $participant7 = [
            'join_day' => 1,
            'leave_day' => 4,
            'join_timing' => 'dinner',
            'leave_timing' => 'return_bus',
        ];
        $adjustment7 = $this->calculateAutoMealAdjustment($camp, $participant7);
        // 1日目昼食は宿泊に含まれないので調整なし
        $expected7 = 0;
        $this->assertEqual($adjustment7['total'], $expected7, "1日目夕食から参加: 調整なし = {$expected7}円");

        // ケース8: 1日目往路バスから3日目朝食まで（2泊）
        // 1泊目（宿泊）: 1日目夕食○、2日目朝食○、2日目昼食○
        // 2泊目（宿泊）: 2日目夕食○、3日目朝食○、3日目昼食× → 3日目昼食欠食
        // 3泊目（不宿泊）: 3日目夕食×、4日目朝食×、4日目昼食× → 調整なし
        $participant8 = [
            'join_day' => 1,
            'leave_day' => 3,
            'join_timing' => 'outbound_bus',
            'leave_timing' => 'breakfast',
        ];
        $adjustment8 = $this->calculateAutoMealAdjustment($camp, $participant8);
        // 3日目昼食欠食 -800
        $expected8 = -800;
        $this->assertEqual($adjustment8['total'], $expected8, "1日目往路バス〜3日目朝食: 3日目昼食欠食 = {$expected8}円");

        // ケース9: 2日目午後イベントから3日目昼食まで（1泊）
        // 1泊目（不宿泊）: 1日目夕食×、2日目朝食×、2日目昼食× → 調整なし（食べない）
        // 2泊目（宿泊）: 2日目夕食○、3日目朝食○、3日目昼食○ → 調整なし
        // 3泊目（不宿泊）: 3日目夕食×、4日目朝食×、4日目昼食× → 調整なし
        $participant9 = [
            'join_day' => 2,
            'leave_day' => 3,
            'join_timing' => 'afternoon',
            'leave_timing' => 'lunch',
        ];
        $adjustment9 = $this->calculateAutoMealAdjustment($camp, $participant9);
        $expected9 = 0;
        $this->assertEqual($adjustment9['total'], $expected9, "2日目午後〜3日目昼食: 調整なし = {$expected9}円");

        // ケース10: 2日目夜から3日目夜まで（1泊）- ユーザー指摘ケース
        // 1泊目（不宿泊）: 1日目夕食×、2日目朝食×、2日目昼食× → 調整なし
        // 2泊目（宿泊）: 2日目夕食×（夜から参加）、3日目朝食○、3日目昼食○ → 2日目夕食欠食
        // 3泊目（不宿泊）: 3日目夕食○（夜まで参加）、4日目朝食×、4日目昼食× → 3日目夕食追加
        $participant10 = [
            'join_day' => 2,
            'leave_day' => 3,
            'join_timing' => 'night',
            'leave_timing' => 'night',
        ];
        $adjustment10 = $this->calculateAutoMealAdjustment($camp, $participant10);
        // 2日目夕食欠食 -1000、3日目夕食追加 +1200
        $expected10 = -1000 + 1200;
        $this->assertEqual($adjustment10['total'], $expected10, "2日目夜〜3日目夜: 2日目夕食欠食 + 3日目夕食追加 = {$expected10}円");
    }

    /**
     * 自動食事調整を計算（宿泊ベース - CalculationService.phpのロジックを再現）
     *
     * ※1日目昼食は宿泊に含まれないため、自動調整の対象外
     */
    private function calculateAutoMealAdjustment(array $camp, array $participant): array
    {
        $total = 0;
        $details = [];
        $totalNights = $camp['nights'];

        // 各泊ごとに食事を計算
        for ($night = 1; $night <= $totalNights; $night++) {
            $isStaying = $this->isParticipantStayingNight($participant, $night);

            $dinnerDay = $night;
            $breakfastDay = $night + 1;
            $lunchDay = $night + 1;

            // N日目夕食
            $eatsDinner = $this->doesEatMeal($dinnerDay, $participant['leave_day'], 'dinner', $participant['join_timing'], $participant['leave_timing'], $participant['join_day']);
            if ($isStaying && !$eatsDinner) {
                $removePrice = $camp['dinner_remove_price'] ?? 0;
                if ($removePrice > 0) {
                    $total -= $removePrice;
                    $details[] = "{$dinnerDay}日目夕食欠食 -{$removePrice}円";
                }
            } elseif (!$isStaying && $eatsDinner) {
                $addPrice = $camp['dinner_add_price'] ?? 0;
                if ($addPrice > 0) {
                    $total += $addPrice;
                    $details[] = "{$dinnerDay}日目夕食追加 +{$addPrice}円";
                }
            }

            // (N+1)日目朝食
            $eatsBreakfast = $this->doesEatMeal($breakfastDay, $participant['leave_day'], 'breakfast', $participant['join_timing'], $participant['leave_timing'], $participant['join_day']);
            if ($isStaying && !$eatsBreakfast) {
                $removePrice = $camp['breakfast_remove_price'] ?? 0;
                if ($removePrice > 0) {
                    $total -= $removePrice;
                    $details[] = "{$breakfastDay}日目朝食欠食 -{$removePrice}円";
                }
            } elseif (!$isStaying && $eatsBreakfast) {
                $addPrice = $camp['breakfast_add_price'] ?? 0;
                if ($addPrice > 0) {
                    $total += $addPrice;
                    $details[] = "{$breakfastDay}日目朝食追加 +{$addPrice}円";
                }
            }

            // (N+1)日目昼食
            $eatsLunch = $this->doesEatMeal($lunchDay, $participant['leave_day'], 'lunch', $participant['join_timing'], $participant['leave_timing'], $participant['join_day']);
            if ($isStaying && !$eatsLunch) {
                $removePrice = $camp['lunch_remove_price'] ?? 0;
                if ($removePrice > 0) {
                    $total -= $removePrice;
                    $details[] = "{$lunchDay}日目昼食欠食 -{$removePrice}円";
                }
            } elseif (!$isStaying && $eatsLunch) {
                $addPrice = $camp['lunch_add_price'] ?? 0;
                if ($addPrice > 0) {
                    $total += $addPrice;
                    $details[] = "{$lunchDay}日目昼食追加 +{$addPrice}円";
                }
            }
        }

        return [
            'total' => $total,
            'details' => $details,
        ];
    }

    /**
     * 参加者がN泊目に宿泊するかどうか判定
     *
     * 「夜まで」(night) = 夜イベントに参加して帰る（その日は泊まらない）
     */
    private function isParticipantStayingNight(array $participant, int $nightNumber): bool
    {
        $joinDay = $participant['join_day'];
        $leaveDay = $participant['leave_day'];

        if ($joinDay > $nightNumber) {
            return false;
        }

        if ($leaveDay > $nightNumber) {
            return true;
        }

        return false;
    }

    /**
     * 宿泊数を計算（CalculationService.phpのロジックを再現）
     *
     * 「夜まで」(night) = 夜イベントに参加して帰る（その日は泊まらない）
     */
    private function calculateNights(int $joinDay, int $leaveDay, string $joinTiming, string $leaveTiming): int
    {
        $nights = $leaveDay - $joinDay;
        return max(0, $nights);
    }

    /**
     * 食事を食べるか判定（CalculationService.phpのロジックを再現）
     */
    private function doesEatMeal(int $day, int $leaveDay, string $mealType, string $joinTiming, string $leaveTiming, int $joinDay = 1): bool
    {

        if ($day < $joinDay || $day > $leaveDay) {
            return false;
        }

        $joinMealOrder = [
            'outbound_bus' => 0,
            'breakfast' => 1,
            'morning' => 2,
            'lunch' => 3,
            'afternoon' => 4,
            'dinner' => 5,
            'night' => 6,
            'lodging' => 7,
        ];

        $leaveMealOrder = [
            'before_breakfast' => 0,
            'breakfast' => 1,
            'morning' => 2,
            'lunch' => 3,
            'afternoon' => 4,
            'dinner' => 5,
            'night' => 6,
            'lodging' => 7,
            'return_bus' => 8,
        ];

        $mealOrderValue = [
            'breakfast' => 1,
            'lunch' => 3,
            'dinner' => 5,
        ];

        $mealValue = $mealOrderValue[$mealType] ?? 0;

        if ($day === $joinDay) {
            $joinValue = $joinMealOrder[$joinTiming] ?? 0;
            if ($mealValue < $joinValue) {
                return false;
            }
        }

        if ($day === $leaveDay) {
            $leaveValue = $leaveMealOrder[$leaveTiming] ?? 8;
            if ($mealValue > $leaveValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * イベントに参加するか判定（CalculationService.phpのロジックを再現）
     */
    private function doesAttendEvent(int $day, int $leaveDay, string $slotType, string $joinTiming, string $leaveTiming, int $joinDay = 1): bool
    {

        if ($day < $joinDay || $day > $leaveDay) {
            return false;
        }

        $joinEventOrder = [
            'outbound_bus' => 0,
            'breakfast' => 1,
            'morning' => 2,
            'lunch' => 3,
            'afternoon' => 4,
            'dinner' => 5,
            'night' => 6,
            'lodging' => 7,
        ];

        $leaveEventOrder = [
            'before_breakfast' => 0,
            'breakfast' => 1,
            'morning' => 2,
            'lunch' => 3,
            'afternoon' => 4,
            'dinner' => 5,
            'night' => 6,
            'lodging' => 7,
            'return_bus' => 8,
        ];

        $eventOrderValue = [
            'morning' => 2,
            'afternoon' => 4,
            'banquet' => 6,
        ];

        $eventValue = $eventOrderValue[$slotType] ?? 0;

        if ($day === $joinDay) {
            $joinValue = $joinEventOrder[$joinTiming] ?? 0;
            if ($eventValue < $joinValue) {
                return false;
            }
        }

        if ($day === $leaveDay) {
            $leaveValue = $leaveEventOrder[$leaveTiming] ?? 8;
            if ($eventValue > $leaveValue) {
                return false;
            }
        }

        return true;
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
            exit(1);
        } else {
            echo "\n✓ すべてのテストが成功しました！\n";
            exit(0);
        }
    }
}

// テスト実行
$test = new TimingLogicTest();
$test->run();
