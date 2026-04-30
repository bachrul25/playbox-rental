<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\PartnershipReport;
use App\Models\Partner;
use App\Models\Playbox;
use App\Models\PrivateReport;
use App\Models\Rental;
use Carbon\Carbon;

class DashboardRepository
{
    public function summary(): array
    {
        $today = now()->toDateString();
        $startMonth = now()->startOfMonth()->toDateString();
        $endMonth = now()->endOfMonth()->toDateString();

        $todayIncome = (float) Rental::whereDate('rental_date', $today)->sum('total_income');
        $monthIncome = (float) Rental::whereDate('rental_date', '>=', $startMonth)->whereDate('rental_date', '<=', $endMonth)->sum('total_income');
        $totalRentals = Rental::count();
        $activePlayboxes = Playbox::whereIn('status', ['tersedia', 'disewa'])->count();
        $totalPartners = Partner::where('status', 'aktif')->count();

        $privateIncome = (float) PrivateReport::whereDate('report_date', '>=', $startMonth)->whereDate('report_date', '<=', $endMonth)->sum('total_income');
        $partnershipIncome = (float) PartnershipReport::whereDate('report_date', '>=', $startMonth)->whereDate('report_date', '<=', $endMonth)->sum('total_income');
        $maintenanceCost = (float) Expense::whereDate('expense_date', '>=', $startMonth)->whereDate('expense_date', '<=', $endMonth)
            ->whereIn('type', ['maintenance', 'perawatan', 'kerusakan'])
            ->sum('amount');
        $ownerProfit = (float) PrivateReport::whereDate('report_date', '>=', $startMonth)->whereDate('report_date', '<=', $endMonth)->sum('owner_profit')
            + (float) PartnershipReport::whereDate('report_date', '>=', $startMonth)->whereDate('report_date', '<=', $endMonth)->sum('owner_share');

        return compact(
            'todayIncome',
            'monthIncome',
            'totalRentals',
            'activePlayboxes',
            'totalPartners',
            'privateIncome',
            'partnershipIncome',
            'maintenanceCost',
            'ownerProfit'
        );
    }

    /**
     * @return array<string,array<string,float|string>>
     */
    public function monthlyIncomeChart(int $months = 12): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $labels = [];
        $income = [];
        $privateData = [];
        $partnershipData = [];

        for ($i = 0; $i < $months; $i++) {
            $cursor = $start->copy()->addMonths($i);
            $from = $cursor->copy()->startOfMonth()->toDateString();
            $to = $cursor->copy()->endOfMonth()->toDateString();
            $labels[] = $cursor->translatedFormat('M Y');
            $income[] = (float) Rental::whereDate('rental_date', '>=', $from)->whereDate('rental_date', '<=', $to)->sum('total_income');
            $privateData[] = (float) PrivateReport::whereDate('report_date', '>=', $from)->whereDate('report_date', '<=', $to)->sum('total_income');
            $partnershipData[] = (float) PartnershipReport::whereDate('report_date', '>=', $from)->whereDate('report_date', '<=', $to)->sum('total_income');
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'private' => $privateData,
            'partnership' => $partnershipData,
        ];
    }
}
