<?php

namespace Tests\Unit;

use App\Support\RentalCalculator;
use PHPUnit\Framework\TestCase;

class RentalCalculatorTest extends TestCase
{
    public function test_private_breakdown_uses_20_80_split(): void
    {
        $b = RentalCalculator::privateBreakdown(1_000_000);
        $this->assertSame(200_000.0, $b['maintenance']);
        $this->assertSame(800_000.0, $b['owner_profit']);
    }

    public function test_partnership_breakdown_with_sufficient_income(): void
    {
        $b = RentalCalculator::partnershipBreakdown(5_000_000);
        $this->assertSame(800_000.0, $b['staff_cost']);
        $this->assertSame(4_200_000.0, $b['net_income']);
        $this->assertSame(2_100_000.0, $b['owner_share']);
        $this->assertSame(2_100_000.0, $b['partner_share']);
        $this->assertFalse($b['deficit']);
    }

    public function test_partnership_breakdown_with_deficit(): void
    {
        $b = RentalCalculator::partnershipBreakdown(500_000);
        $this->assertSame(0.0, $b['net_income']);
        $this->assertSame(0.0, $b['owner_share']);
        $this->assertSame(0.0, $b['partner_share']);
        $this->assertTrue($b['deficit']);
    }
}
