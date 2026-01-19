<?php
/**
 * 費用計算サービス
 */
class CalculationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 合宿の費用計算を実行
     */
    public function calculate(int $campId): array
    {
        $campModel = new Camp();
        $camp = $campModel->find($campId);

        if (!$camp) {
            throw new Exception('合宿が見つかりません');
        }

        $participantModel = new Participant();
        $participants = $participantModel->getByCampId($campId);

        $timeSlotModel = new TimeSlot();
        $timeSlots = $timeSlotModel->getByCampId($campId);

        $expenseModel = new Expense();
        $expenses = $expenseModel->getByCampId($campId);

        // 各参加者の費用を計算
        $results = [];
        $totalAmount = 0;

        foreach ($participants as $participant) {
            $breakdown = $this->calculateParticipantFee($camp, $participant, $timeSlots, $expenses, $participants);
            $results[] = $breakdown;
            $totalAmount += $breakdown['total'];
        }

        return [
            'camp' => $camp,
            'participants' => $results,
            'summary' => [
                'total_amount' => $totalAmount,
                'participant_count' => count($participants),
                'average_amount' => count($participants) > 0 ? round($totalAmount / count($participants)) : 0,
            ],
        ];
    }

    /**
     * 参加者1人の費用を計算
     */
    private function calculateParticipantFee(
        array $camp,
        array $participant,
        array $timeSlots,
        array $expenses,
        array $allParticipants
    ): array {
        $participantModel = new Participant();
        $slots = $participantModel->getParticipantSlots($participant['id']);
        $mealAdjustments = $participantModel->getMealAdjustments($participant['id']);

        $breakdown = [
            'participant_id' => $participant['id'],
            'name' => $participant['name'],
            'items' => [],
            'total' => 0,
        ];

        // 1. 宿泊費
        $nights = $this->calculateNights($participant, $camp);
        if ($nights > 0) {
            $lodgingFee = $camp['lodging_fee_per_night'] * $nights;
            $breakdown['items'][] = [
                'category' => 'lodging',
                'name' => "宿泊費 ({$nights}泊)",
                'amount' => $lodgingFee,
            ];
            $breakdown['total'] += $lodgingFee;
        }

        // 2. 保険料（固定）
        $breakdown['items'][] = [
            'category' => 'insurance',
            'name' => '保険料',
            'amount' => $camp['insurance_fee'],
        ];
        $breakdown['total'] += $camp['insurance_fee'];

        // 3. 食事調整（タイミングに基づく自動減算 + 手動調整）
        $autoMealAdjustment = $this->calculateAutoMealAdjustment($camp, $participant);
        $manualMealAdjustment = $this->calculateMealAdjustment($camp, $mealAdjustments);
        $totalMealAdjustment = $autoMealAdjustment['total'] + $manualMealAdjustment;

        if ($totalMealAdjustment !== 0 || !empty($autoMealAdjustment['details'])) {
            $allDetails = array_merge(
                $autoMealAdjustment['details'],
                $this->getMealAdjustmentDetails($camp, $mealAdjustments)
            );
            $breakdown['items'][] = [
                'category' => 'meal_adjustment',
                'name' => '食事調整',
                'amount' => $totalMealAdjustment,
                'details' => $allDetails,
            ];
            $breakdown['total'] += $totalMealAdjustment;
        }

        // 4. バス代計算
        // 往復一括設定か別設定かで分岐
        $isBusSeparate = !empty($camp['bus_fee_separate']);
        $outboundUsers = $this->countBusUsers($allParticipants, 'outbound');
        $returnUsers = $this->countBusUsers($allParticipants, 'return');

        if ($isBusSeparate) {
            // 往路/復路別設定
            if ($participant['use_outbound_bus'] && $camp['bus_fee_outbound'] && $outboundUsers > 0) {
                $busFee = round($camp['bus_fee_outbound'] / $outboundUsers);
                $breakdown['items'][] = [
                    'category' => 'bus',
                    'name' => "往路バス代 (1/{$outboundUsers})",
                    'amount' => $busFee,
                ];
                $breakdown['total'] += $busFee;
            }
            if ($participant['use_return_bus'] && $camp['bus_fee_return'] && $returnUsers > 0) {
                $busFee = round($camp['bus_fee_return'] / $returnUsers);
                $breakdown['items'][] = [
                    'category' => 'bus',
                    'name' => "復路バス代 (1/{$returnUsers})",
                    'amount' => $busFee,
                ];
                $breakdown['total'] += $busFee;
            }
        } else {
            // 往復一括設定
            if ($camp['bus_fee_round_trip']) {
                $useOutbound = $participant['use_outbound_bus'];
                $useReturn = $participant['use_return_bus'];

                if ($useOutbound && $useReturn) {
                    // 往復両方利用: 往復料金 ÷ 往復利用者数
                    $roundTripUsers = $this->countBusUsers($allParticipants, 'round_trip');
                    if ($roundTripUsers > 0) {
                        $busFee = round($camp['bus_fee_round_trip'] / $roundTripUsers);
                        $breakdown['items'][] = [
                            'category' => 'bus',
                            'name' => "バス代往復 (1/{$roundTripUsers})",
                            'amount' => $busFee,
                        ];
                        $breakdown['total'] += $busFee;
                    }
                } elseif ($useOutbound && $outboundUsers > 0) {
                    // 往路のみ: 往復料金の半分 ÷ 往路利用者数
                    $busFee = round(($camp['bus_fee_round_trip'] / 2) / $outboundUsers);
                    $breakdown['items'][] = [
                        'category' => 'bus',
                        'name' => "往路バス代 (1/{$outboundUsers})",
                        'amount' => $busFee,
                    ];
                    $breakdown['total'] += $busFee;
                } elseif ($useReturn && $returnUsers > 0) {
                    // 復路のみ: 往復料金の半分 ÷ 復路利用者数
                    $busFee = round(($camp['bus_fee_round_trip'] / 2) / $returnUsers);
                    $breakdown['items'][] = [
                        'category' => 'bus',
                        'name' => "復路バス代 (1/{$returnUsers})",
                        'amount' => $busFee,
                    ];
                    $breakdown['total'] += $busFee;
                }
            }
        }

        // 5. 高速代（往路）- バス利用者のみ
        if ($participant['use_outbound_bus'] && $camp['highway_fee_outbound'] && $outboundUsers > 0) {
            $highwayFee = round($camp['highway_fee_outbound'] / $outboundUsers);
            $breakdown['items'][] = [
                'category' => 'highway',
                'name' => "往路高速代 (1/{$outboundUsers})",
                'amount' => $highwayFee,
            ];
            $breakdown['total'] += $highwayFee;
        }

        // 6. 高速代（復路）- バス利用者のみ
        if ($participant['use_return_bus'] && $camp['highway_fee_return'] && $returnUsers > 0) {
            $highwayFee = round($camp['highway_fee_return'] / $returnUsers);
            $breakdown['items'][] = [
                'category' => 'highway',
                'name' => "復路高速代 (1/{$returnUsers})",
                'amount' => $highwayFee,
            ];
            $breakdown['total'] += $highwayFee;
        }

        // 7. レンタカー代
        if ($participant['use_rental_car'] && $camp['use_rental_car']) {
            $rentalCarUsers = $this->countRentalCarUsers($allParticipants);
            if ($rentalCarUsers > 0) {
                // レンタカー代
                if ($camp['rental_car_fee']) {
                    $rentalFee = round($camp['rental_car_fee'] / $rentalCarUsers);
                    $breakdown['items'][] = [
                        'category' => 'rental_car',
                        'name' => "レンタカー代 (1/{$rentalCarUsers})",
                        'amount' => $rentalFee,
                    ];
                    $breakdown['total'] += $rentalFee;
                }
                // レンタカー高速代
                if ($camp['rental_car_highway_fee']) {
                    $rentalHighwayFee = round($camp['rental_car_highway_fee'] / $rentalCarUsers);
                    $breakdown['items'][] = [
                        'category' => 'rental_car_highway',
                        'name' => "レンタカー高速代 (1/{$rentalCarUsers})",
                        'amount' => $rentalHighwayFee,
                    ];
                    $breakdown['total'] += $rentalHighwayFee;
                }
            }
        }

        // 8. 施設利用料（コマごと）
        foreach ($timeSlots as $slot) {
            if ($slot['facility_fee'] && $slot['facility_fee'] > 0 && $slot['slot_type'] !== 'outbound' && $slot['slot_type'] !== 'return') {
                // この参加者がこのスロットに参加しているか（タイミングベースで判定）
                $isAttending = $this->doesParticipantAttendEvent($participant, $slot['day_number'], $slot['slot_type']);

                if ($isAttending) {
                    $slotName = $this->getSlotDisplayName($slot);

                    // 宴会場の場合は1人あたり単価（割り勘ではない）
                    if ($slot['activity_type'] === 'banquet') {
                        $slotFee = $slot['facility_fee'];
                        $breakdown['items'][] = [
                            'category' => 'facility',
                            'name' => "{$slotName}",
                            'amount' => $slotFee,
                        ];
                        $breakdown['total'] += $slotFee;
                    } else {
                        // テニスコート、体育館、その他は参加者で割り勘
                        $attendeeCount = $this->countSlotAttendeesFromTiming($slot, $allParticipants);
                        if ($attendeeCount > 0) {
                            $slotFee = round($slot['facility_fee'] / $attendeeCount);
                            $breakdown['items'][] = [
                                'category' => 'facility',
                                'name' => "{$slotName} (1/{$attendeeCount})",
                                'amount' => $slotFee,
                            ];
                            $breakdown['total'] += $slotFee;
                        }
                    }
                }
            }
        }

        // 9. 雑費
        foreach ($expenses as $expense) {
            $targetCount = $this->countExpenseTargetsFromTiming($expense, $allParticipants);
            $isTarget = $this->isParticipantExpenseTargetFromTiming($expense, $participant);

            if ($isTarget && $targetCount > 0) {
                $expenseFee = round($expense['amount'] / $targetCount);
                $breakdown['items'][] = [
                    'category' => 'expense',
                    'name' => "{$expense['name']} (1/{$targetCount})",
                    'amount' => $expenseFee,
                ];
                $breakdown['total'] += $expenseFee;
            }
        }

        return $breakdown;
    }

    /**
     * 宿泊数を計算
     *
     * 宿泊数 = leaveDay - joinDay
     * 「夜まで」(night) = 夜イベントに参加して帰る（その日は泊まらない）
     */
    private function calculateNights(array $participant, array $camp): int
    {
        $joinDay = $participant['join_day'];
        $leaveDay = $participant['leave_day'];

        // 例: 1日目参加、3日目離脱 → 1泊目(1日目夜)、2泊目(2日目夜) = 2泊
        // 例: 1日目参加、2日目夜まで → 1泊目(1日目夜)のみ = 1泊
        $nights = $leaveDay - $joinDay;

        return max(0, $nights);
    }

    /**
     * 参加者がその日の特定の食事を食べるかどうか判定
     *
     * @param array $participant 参加者データ
     * @param int $day 日数
     * @param string $mealType 食事タイプ (breakfast, lunch, dinner)
     * @return bool
     */
    private function doesParticipantEatMeal(array $participant, int $day, string $mealType): bool
    {
        $joinDay = $participant['join_day'];
        $leaveDay = $participant['leave_day'];
        $joinTiming = $participant['join_timing'];
        $leaveTiming = $participant['leave_timing'];

        // 参加期間外
        if ($day < $joinDay || $day > $leaveDay) {
            return false;
        }

        // 食事の順序定義（参加開始用）
        // その食事「から」参加 = その食事を食べる
        $joinMealOrder = [
            'outbound_bus' => 0,
            'breakfast' => 1,
            'morning' => 2,      // 午前イベントから = 朝食を食べない
            'lunch' => 3,
            'afternoon' => 4,    // 午後イベントから = 昼食を食べない
            'dinner' => 5,
            'night' => 6,        // 夜から = 夕食を食べない
            'lodging' => 7,
        ];

        // 食事の順序定義（離脱用）
        // その食事「まで」参加 = その食事を食べる
        $leaveMealOrder = [
            'before_breakfast' => 0,  // 朝食前まで = 朝食を食べない
            'breakfast' => 1,          // 朝食まで = 朝食を食べる
            'morning' => 2,            // 午前イベントまで = 昼食を食べない
            'lunch' => 3,              // 昼食まで = 昼食を食べる
            'afternoon' => 4,          // 午後イベントまで = 夕食を食べない
            'dinner' => 5,             // 夕食まで = 夕食を食べる
            'night' => 6,              // 夜まで = 食事は全て食べている
            'lodging' => 7,            // 宿泊まで = 食事は全て食べている
            'return_bus' => 8,         // 復路バスまで = 食事は全て食べている（最終日）
        ];

        // 食事タイプの順序値
        $mealOrderValue = [
            'breakfast' => 1,
            'lunch' => 3,
            'dinner' => 5,
        ];

        $mealValue = $mealOrderValue[$mealType] ?? 0;

        // 参加開始日のチェック
        if ($day === $joinDay) {
            $joinValue = $joinMealOrder[$joinTiming] ?? 0;
            // 参加開始タイミングより前の食事は食べない
            if ($mealValue < $joinValue) {
                return false;
            }
        }

        // 離脱日のチェック
        if ($day === $leaveDay) {
            $leaveValue = $leaveMealOrder[$leaveTiming] ?? 8;
            // 離脱タイミングより後の食事は食べない
            if ($mealValue > $leaveValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * 参加者がその日の特定のイベントに参加するかどうか判定
     *
     * @param array $participant 参加者データ
     * @param int $day 日数
     * @param string $slotType スロットタイプ (morning, afternoon, banquet)
     * @return bool
     */
    private function doesParticipantAttendEvent(array $participant, int $day, string $slotType): bool
    {
        $joinDay = $participant['join_day'];
        $leaveDay = $participant['leave_day'];
        $joinTiming = $participant['join_timing'];
        $leaveTiming = $participant['leave_timing'];

        // 参加期間外
        if ($day < $joinDay || $day > $leaveDay) {
            return false;
        }

        // イベントの順序定義（参加開始用）
        $joinEventOrder = [
            'outbound_bus' => 0,
            'breakfast' => 1,
            'morning' => 2,      // 午前イベントから参加
            'lunch' => 3,
            'afternoon' => 4,    // 午後イベントから参加
            'dinner' => 5,
            'night' => 6,        // 夜イベントから参加
            'lodging' => 7,
        ];

        // イベントの順序定義（離脱用）
        $leaveEventOrder = [
            'before_breakfast' => 0,
            'breakfast' => 1,
            'morning' => 2,      // 午前イベントまで参加
            'lunch' => 3,
            'afternoon' => 4,    // 午後イベントまで参加
            'dinner' => 5,
            'night' => 6,        // 夜イベントまで参加
            'lodging' => 7,
            'return_bus' => 8,
        ];

        // イベントタイプの順序値
        $eventOrderValue = [
            'morning' => 2,
            'afternoon' => 4,
            'banquet' => 6,  // 宴会は夜イベント扱い
        ];

        $eventValue = $eventOrderValue[$slotType] ?? 0;

        // 参加開始日のチェック
        if ($day === $joinDay) {
            $joinValue = $joinEventOrder[$joinTiming] ?? 0;
            // 参加開始タイミングより前のイベントには参加しない
            if ($eventValue < $joinValue) {
                return false;
            }
        }

        // 離脱日のチェック
        if ($day === $leaveDay) {
            $leaveValue = $leaveEventOrder[$leaveTiming] ?? 8;
            // 離脱タイミングより後のイベントには参加しない
            if ($eventValue > $leaveValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * タイミングに基づく自動食事調整額を計算
     *
     * 宿泊に食事が紐づいているため、宿泊ベースで計算する：
     * - N泊目に含まれる食事: N日目夕食、(N+1)日目朝食、(N+1)日目昼食
     * - 宿泊している泊の食事を食べなかったら → 欠食（減算）
     * - 宿泊していない泊の食事を食べたら → 追加（加算）
     *
     * ※1日目昼食は宿泊に含まれないため、自動調整の対象外
     */
    private function calculateAutoMealAdjustment(array $camp, array $participant): array
    {
        $total = 0;
        $details = [];

        // 合宿の総泊数
        $totalNights = $camp['nights'];

        // 各泊ごとに食事を計算
        for ($night = 1; $night <= $totalNights; $night++) {
            $isStaying = $this->isParticipantStayingNight($participant, $night);

            // N泊目に含まれる食事:
            // - N日目夕食
            // - (N+1)日目朝食
            // - (N+1)日目昼食

            $dinnerDay = $night;        // N日目夕食
            $breakfastDay = $night + 1; // (N+1)日目朝食
            $lunchDay = $night + 1;     // (N+1)日目昼食

            // N日目夕食
            $eatsDinner = $this->doesParticipantEatMeal($participant, $dinnerDay, 'dinner');
            if ($isStaying && !$eatsDinner) {
                // 宿泊するのに夕食を食べない → 欠食
                $removePrice = $camp['dinner_remove_price'] ?? 0;
                if ($removePrice > 0) {
                    $total -= $removePrice;
                    $details[] = "{$dinnerDay}日目夕食欠食 -{$removePrice}円";
                }
            } elseif (!$isStaying && $eatsDinner) {
                // 宿泊しないのに夕食を食べる → 追加
                $addPrice = $camp['dinner_add_price'] ?? 0;
                if ($addPrice > 0) {
                    $total += $addPrice;
                    $details[] = "{$dinnerDay}日目夕食追加 +{$addPrice}円";
                }
            }

            // (N+1)日目朝食
            $eatsBreakfast = $this->doesParticipantEatMeal($participant, $breakfastDay, 'breakfast');
            if ($isStaying && !$eatsBreakfast) {
                // 宿泊するのに朝食を食べない → 欠食
                $removePrice = $camp['breakfast_remove_price'] ?? 0;
                if ($removePrice > 0) {
                    $total -= $removePrice;
                    $details[] = "{$breakfastDay}日目朝食欠食 -{$removePrice}円";
                }
            } elseif (!$isStaying && $eatsBreakfast) {
                // 宿泊しないのに朝食を食べる → 追加
                $addPrice = $camp['breakfast_add_price'] ?? 0;
                if ($addPrice > 0) {
                    $total += $addPrice;
                    $details[] = "{$breakfastDay}日目朝食追加 +{$addPrice}円";
                }
            }

            // (N+1)日目昼食
            $eatsLunch = $this->doesParticipantEatMeal($participant, $lunchDay, 'lunch');
            if ($isStaying && !$eatsLunch) {
                // 宿泊するのに昼食を食べない → 欠食
                $removePrice = $camp['lunch_remove_price'] ?? 0;
                if ($removePrice > 0) {
                    $total -= $removePrice;
                    $details[] = "{$lunchDay}日目昼食欠食 -{$removePrice}円";
                }
            } elseif (!$isStaying && $eatsLunch) {
                // 宿泊しないのに昼食を食べる → 追加
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
     * N泊目 = N日目の夜に宿泊する
     * 例: 1泊目 = 1日目の夜に宿泊
     *
     * 「夜まで」(night) = 夜イベントに参加して帰る（その日は泊まらない）
     */
    private function isParticipantStayingNight(array $participant, int $nightNumber): bool
    {
        $joinDay = $participant['join_day'];
        $leaveDay = $participant['leave_day'];

        // N泊目に宿泊するには joinDay <= nightNumber が必要
        if ($joinDay > $nightNumber) {
            return false;
        }

        // leaveDay > nightNumber なら宿泊
        if ($leaveDay > $nightNumber) {
            return true;
        }

        return false;
    }

    /**
     * 食事調整額を計算（手動調整）
     */
    private function calculateMealAdjustment(array $camp, array $adjustments): int
    {
        $total = 0;

        foreach ($adjustments as $adj) {
            $mealType = $adj['meal_type'];
            $adjType = $adj['adjustment_type'];

            if ($adjType === 'add') {
                $total += $camp[$mealType . '_add_price'] ?? 0;
            } elseif ($adjType === 'remove') {
                $total -= $camp[$mealType . '_remove_price'] ?? 0;
            }
        }

        return $total;
    }

    /**
     * 食事調整の詳細を取得
     */
    private function getMealAdjustmentDetails(array $camp, array $adjustments): array
    {
        $details = [];

        foreach ($adjustments as $adj) {
            $mealNames = ['breakfast' => '朝食', 'lunch' => '昼食', 'dinner' => '夕食'];
            $mealName = $mealNames[$adj['meal_type']] ?? $adj['meal_type'];

            if ($adj['adjustment_type'] === 'add') {
                $amount = $camp[$adj['meal_type'] . '_add_price'] ?? 0;
                $details[] = "{$adj['day_number']}日目{$mealName}追加 +{$amount}円";
            } else {
                $amount = $camp[$adj['meal_type'] . '_remove_price'] ?? 0;
                $details[] = "{$adj['day_number']}日目{$mealName}欠食 -{$amount}円";
            }
        }

        return $details;
    }

    /**
     * バス利用者数をカウント
     */
    private function countBusUsers(array $participants, string $direction): int
    {
        $count = 0;

        foreach ($participants as $p) {
            if ($direction === 'round_trip') {
                // 往復両方利用する人
                if ($p['use_outbound_bus'] && $p['use_return_bus']) {
                    $count++;
                }
            } elseif ($direction === 'outbound') {
                if ($p['use_outbound_bus']) {
                    $count++;
                }
            } elseif ($direction === 'return') {
                if ($p['use_return_bus']) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * レンタカー利用者数をカウント
     */
    private function countRentalCarUsers(array $participants): int
    {
        $count = 0;

        foreach ($participants as $p) {
            if ($p['use_rental_car']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 参加者がスロットに参加しているかチェック
     */
    private function isParticipantAttendingSlot(array $participantSlots, int $slotId): bool
    {
        foreach ($participantSlots as $ps) {
            if ($ps['time_slot_id'] == $slotId && $ps['is_attending']) {
                return true;
            }
        }
        return false;
    }

    /**
     * スロット参加者数をカウント
     */
    private function countSlotAttendees(int $slotId, array $allParticipants): int
    {
        $count = 0;
        $participantModel = new Participant();

        foreach ($allParticipants as $p) {
            $slots = $participantModel->getParticipantSlots($p['id']);
            if ($this->isParticipantAttendingSlot($slots, $slotId)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * スロット表示名を取得
     */
    private function getSlotDisplayName(array $slot): string
    {
        $slotTypes = [
            'morning' => '午前',
            'afternoon' => '午後',
            'banquet' => '宴会場',
        ];
        $slotTypeName = $slotTypes[$slot['slot_type']] ?? $slot['slot_type'];

        return "{$slot['day_number']}日目{$slotTypeName}";
    }

    /**
     * 雑費の対象者数をカウント
     */
    private function countExpenseTargets(array $expense, array $allParticipants, array $timeSlots): int
    {
        if ($expense['target_type'] === 'all') {
            return count($allParticipants);
        }

        // 特定スロットの参加者
        $participantModel = new Participant();
        $count = 0;

        foreach ($allParticipants as $p) {
            $slots = $participantModel->getParticipantSlots($p['id']);
            if ($this->isParticipantExpenseTarget($expense, $p, $slots)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 参加者が雑費の対象かチェック
     */
    private function isParticipantExpenseTarget(array $expense, array $participant, array $participantSlots): bool
    {
        if ($expense['target_type'] === 'all') {
            return true;
        }

        // 特定スロットの参加者のみ
        if ($expense['target_type'] === 'slot' && $expense['target_day'] && $expense['target_slot']) {
            foreach ($participantSlots as $ps) {
                if ($ps['day_number'] == $expense['target_day'] &&
                    $ps['slot_type'] == $expense['target_slot'] &&
                    $ps['is_attending']) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * スロット参加者数をタイミングベースでカウント
     */
    private function countSlotAttendeesFromTiming(array $slot, array $allParticipants): int
    {
        $count = 0;

        foreach ($allParticipants as $p) {
            if ($this->doesParticipantAttendEvent($p, $slot['day_number'], $slot['slot_type'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 雑費の対象者数をタイミングベースでカウント
     */
    private function countExpenseTargetsFromTiming(array $expense, array $allParticipants): int
    {
        if ($expense['target_type'] === 'all') {
            return count($allParticipants);
        }

        // 特定スロットの参加者
        $count = 0;

        foreach ($allParticipants as $p) {
            if ($this->isParticipantExpenseTargetFromTiming($expense, $p)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 参加者が雑費の対象かタイミングベースでチェック
     */
    private function isParticipantExpenseTargetFromTiming(array $expense, array $participant): bool
    {
        if ($expense['target_type'] === 'all') {
            return true;
        }

        // 特定スロットの参加者のみ
        if ($expense['target_type'] === 'slot' && $expense['target_day'] && $expense['target_slot']) {
            return $this->doesParticipantAttendEvent($participant, $expense['target_day'], $expense['target_slot']);
        }

        return true;
    }

    /**
     * 途中参加・途中抜け参加者の参加スケジュール表を生成
     *
     * スクリーンショットのような表形式のデータを返す
     */
    public function generatePartialParticipationSchedule(int $campId): array
    {
        $campModel = new Camp();
        $camp = $campModel->find($campId);

        if (!$camp) {
            throw new Exception('合宿が見つかりません');
        }

        $participantModel = new Participant();
        $participants = $participantModel->getByCampId($campId);

        // フル参加でない参加者のみフィルタリング
        $totalDays = $camp['nights'] + 1;
        $partialParticipants = array_filter($participants, function($p) use ($totalDays) {
            // 1日目往路バスから最終日復路バスまでがフル参加
            $isFullJoin = ($p['join_day'] == 1 && $p['join_timing'] === 'outbound_bus');
            $isFullLeave = ($p['leave_day'] == $totalDays && $p['leave_timing'] === 'return_bus');
            return !($isFullJoin && $isFullLeave);
        });

        // ヘッダー（列）を生成
        $headers = $this->generateScheduleHeaders($camp);

        // 各参加者の参加状況を生成
        $rows = [];
        foreach ($partialParticipants as $participant) {
            $rows[] = $this->generateParticipantScheduleRow($camp, $participant, $headers);
        }

        // 集計行を生成（全参加者の参加人数）
        $totals = $this->generateScheduleTotalsForAllParticipants($camp, $participants, $headers);

        return [
            'camp' => $camp,
            'headers' => $headers,
            'rows' => $rows,
            'totals' => $totals,
            'partial_count' => count($partialParticipants),
            'total_count' => count($participants),
        ];
    }

    /**
     * スケジュール表のヘッダーを生成
     *
     * 最終日は昼食後に復路バスで帰るだけなので、午後練はなし
     */
    private function generateScheduleHeaders(array $camp): array
    {
        $headers = [];
        $totalDays = $camp['nights'] + 1;

        for ($day = 1; $day <= $totalDays; $day++) {
            $dayHeaders = [];
            $isLastDay = ($day === $totalDays);

            // 1日目のみ：往路バス
            if ($day === 1) {
                $dayHeaders[] = ['key' => "day{$day}_outbound_bus", 'label' => 'バス(往)', 'type' => 'bus'];
            }

            // 1日目以外：朝食
            if ($day > 1) {
                $dayHeaders[] = ['key' => "day{$day}_breakfast", 'label' => '朝食', 'type' => 'meal'];
            }

            // 午前練
            $dayHeaders[] = ['key' => "day{$day}_morning", 'label' => '午前練', 'type' => 'event'];

            // 昼食
            $dayHeaders[] = ['key' => "day{$day}_lunch", 'label' => '昼食', 'type' => 'meal'];

            // 最終日以外：午後練、夕食、夜企画、宿泊
            if (!$isLastDay) {
                $dayHeaders[] = ['key' => "day{$day}_afternoon", 'label' => '午後練', 'type' => 'event'];
                $dayHeaders[] = ['key' => "day{$day}_dinner", 'label' => '夕食', 'type' => 'meal'];
                $dayHeaders[] = ['key' => "day{$day}_night", 'label' => '夜企画', 'type' => 'event'];
                $dayHeaders[] = ['key' => "day{$day}_lodging", 'label' => '宿泊', 'type' => 'lodging'];
            }

            // 最終日のみ：復路バス（昼食後）
            if ($isLastDay) {
                $dayHeaders[] = ['key' => "day{$day}_return_bus", 'label' => 'バス(復)', 'type' => 'bus'];
            }

            $headers[] = [
                'day' => $day,
                'columns' => $dayHeaders,
            ];
        }

        return $headers;
    }

    /**
     * 参加者のスケジュール行を生成
     */
    private function generateParticipantScheduleRow(array $camp, array $participant, array $headers): array
    {
        $row = [
            'participant_id' => $participant['id'],
            'name' => $participant['name'],
            'gender' => $participant['gender'],
            'grade' => $participant['grade'],
            'description' => $this->generateParticipationDescription($participant, $camp),
            'schedule' => [],
        ];

        $totalDays = $camp['nights'] + 1;

        foreach ($headers as $dayHeader) {
            $day = $dayHeader['day'];
            foreach ($dayHeader['columns'] as $col) {
                $key = $col['key'];
                $type = $col['type'];

                $attends = false;

                switch ($type) {
                    case 'bus':
                        if (strpos($key, 'outbound') !== false) {
                            $attends = $participant['use_outbound_bus'] &&
                                       $participant['join_day'] == 1 &&
                                       $participant['join_timing'] === 'outbound_bus';
                        } else {
                            $attends = $participant['use_return_bus'] &&
                                       $participant['leave_day'] == $totalDays &&
                                       $participant['leave_timing'] === 'return_bus';
                        }
                        break;

                    case 'meal':
                        $mealType = '';
                        if (strpos($key, 'breakfast') !== false) $mealType = 'breakfast';
                        elseif (strpos($key, 'lunch') !== false) $mealType = 'lunch';
                        elseif (strpos($key, 'dinner') !== false) $mealType = 'dinner';
                        $attends = $this->doesParticipantEatMeal($participant, $day, $mealType);
                        break;

                    case 'event':
                        $slotType = '';
                        if (strpos($key, 'morning') !== false) $slotType = 'morning';
                        elseif (strpos($key, 'afternoon') !== false) $slotType = 'afternoon';
                        elseif (strpos($key, 'night') !== false) $slotType = 'banquet';
                        $attends = $this->doesParticipantAttendEvent($participant, $day, $slotType);
                        break;

                    case 'lodging':
                        $attends = $this->isParticipantStayingNight($participant, $day);
                        break;
                }

                $row['schedule'][$key] = $attends;
            }
        }

        return $row;
    }

    /**
     * 参加状況の説明文を生成
     */
    private function generateParticipationDescription(array $participant, array $camp): string
    {
        $totalDays = $camp['nights'] + 1;
        $joinDay = $participant['join_day'];
        $leaveDay = $participant['leave_day'];
        $joinTiming = $participant['join_timing'];
        $leaveTiming = $participant['leave_timing'];

        $joinTimingLabels = [
            'outbound_bus' => '往路バス',
            'breakfast' => '朝食',
            'morning' => '午前練',
            'lunch' => '昼食',
            'afternoon' => '午後練',
            'dinner' => '夕食',
            'night' => '夜',
        ];

        $leaveTimingLabels = [
            'before_breakfast' => '朝食前',
            'breakfast' => '朝食',
            'morning' => '午前練',
            'lunch' => '昼食',
            'afternoon' => '午後練',
            'dinner' => '夕食',
            'night' => '夜',
            'return_bus' => '復路バス',
        ];

        $joinLabel = $joinTimingLabels[$joinTiming] ?? $joinTiming;
        $leaveLabel = $leaveTimingLabels[$leaveTiming] ?? $leaveTiming;

        return "{$joinDay}日目{$joinLabel}から参加、{$leaveDay}日目{$leaveLabel}で抜ける";
    }

    /**
     * 集計行を生成（全参加者の参加人数）
     */
    private function generateScheduleTotalsForAllParticipants(array $camp, array $participants, array $headers): array
    {
        $totals = [];
        $totalDays = $camp['nights'] + 1;

        foreach ($headers as $dayHeader) {
            $day = $dayHeader['day'];
            foreach ($dayHeader['columns'] as $col) {
                $key = $col['key'];
                $type = $col['type'];
                $count = 0;

                foreach ($participants as $participant) {
                    $attends = false;

                    switch ($type) {
                        case 'bus':
                            if (strpos($key, 'outbound') !== false) {
                                $attends = $participant['use_outbound_bus'] &&
                                           $participant['join_day'] == 1 &&
                                           $participant['join_timing'] === 'outbound_bus';
                            } else {
                                $attends = $participant['use_return_bus'] &&
                                           $participant['leave_day'] == $totalDays &&
                                           $participant['leave_timing'] === 'return_bus';
                            }
                            break;

                        case 'meal':
                            $mealType = '';
                            if (strpos($key, 'breakfast') !== false) $mealType = 'breakfast';
                            elseif (strpos($key, 'lunch') !== false) $mealType = 'lunch';
                            elseif (strpos($key, 'dinner') !== false) $mealType = 'dinner';
                            $attends = $this->doesParticipantEatMeal($participant, $day, $mealType);
                            break;

                        case 'event':
                            $slotType = '';
                            if (strpos($key, 'morning') !== false) $slotType = 'morning';
                            elseif (strpos($key, 'afternoon') !== false) $slotType = 'afternoon';
                            elseif (strpos($key, 'night') !== false) $slotType = 'banquet';
                            $attends = $this->doesParticipantAttendEvent($participant, $day, $slotType);
                            break;

                        case 'lodging':
                            $attends = $this->isParticipantStayingNight($participant, $day);
                            break;
                    }

                    if ($attends) {
                        $count++;
                    }
                }

                $totals[$key] = $count;
            }
        }

        return $totals;
    }
}
