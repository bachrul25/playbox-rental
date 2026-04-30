<?php

namespace App\Support;

class RentalCalculator
{
    public const STAFF_COST = 800000;
    public const PRIVATE_MAINTENANCE_PCT = 20;
    public const PRIVATE_OWNER_PCT = 80;
    public const PARTNERSHIP_SHARE_PCT = 50;

    /**
     * @return array{maintenance:float, owner_profit:float}
     */
    public static function privateBreakdown(float $totalIncome): array
    {
        $maintenance = round($totalIncome * self::PRIVATE_MAINTENANCE_PCT / 100, 2);
        $ownerProfit = round($totalIncome * self::PRIVATE_OWNER_PCT / 100, 2);

        return [
            'maintenance' => $maintenance,
            'owner_profit' => $ownerProfit,
        ];
    }

    /**
     * @return array{staff_cost:float, net_income:float, owner_share:float, partner_share:float, deficit:bool}
     */
    public static function partnershipBreakdown(float $totalIncome, float $staffCost = self::STAFF_COST): array
    {
        $deficit = $totalIncome < $staffCost;
        $netIncome = (float) max(0.0, $totalIncome - $staffCost);
        $ownerShare = round($netIncome * self::PARTNERSHIP_SHARE_PCT / 100, 2);
        $partnerShare = round($netIncome - $ownerShare, 2);

        return [
            'staff_cost' => $staffCost,
            'net_income' => $netIncome,
            'owner_share' => $ownerShare,
            'partner_share' => $partnerShare,
            'deficit' => $deficit,
        ];
    }
}
