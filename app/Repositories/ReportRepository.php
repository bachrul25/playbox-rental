<?php

namespace App\Repositories;

use App\Models\PartnershipReport;
use App\Models\PrivateReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportRepository
{
    /**
     * @return array{from:Carbon, to:Carbon}
     */
    public function resolveRange(string $period, ?string $from = null, ?string $to = null): array
    {
        return match ($period) {
            'harian' => ['from' => now()->startOfDay(), 'to' => now()->endOfDay()],
            'mingguan' => ['from' => now()->startOfWeek(), 'to' => now()->endOfWeek()],
            'bulanan' => ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
            'tahunan' => ['from' => now()->startOfYear(), 'to' => now()->endOfYear()],
            'kustom' => [
                'from' => $from ? Carbon::parse($from)->startOfDay() : now()->startOfMonth(),
                'to' => $to ? Carbon::parse($to)->endOfDay() : now()->endOfMonth(),
            ],
            default => ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
        };
    }

    public function privateQuery(string $period = 'bulanan', ?string $from = null, ?string $to = null): Builder
    {
        $r = $this->resolveRange($period, $from, $to);
        return PrivateReport::query()
            ->with(['rental.playbox', 'rental.user'])
            ->whereDate('report_date', '>=', $r['from']->toDateString())
            ->whereDate('report_date', '<=', $r['to']->toDateString())
            ->orderByDesc('report_date');
    }

    public function partnershipQuery(string $period = 'bulanan', ?string $from = null, ?string $to = null, ?int $partnerId = null): Builder
    {
        $r = $this->resolveRange($period, $from, $to);
        return PartnershipReport::query()
            ->with(['rental.playbox', 'partner'])
            ->whereDate('report_date', '>=', $r['from']->toDateString())
            ->whereDate('report_date', '<=', $r['to']->toDateString())
            ->when($partnerId, fn (Builder $q) => $q->where('partner_id', $partnerId))
            ->orderByDesc('report_date');
    }

    /**
     * @return array{total_income:float, total_maintenance:float, total_owner_profit:float, count:int}
     */
    public function privateSummary(Collection $reports): array
    {
        return [
            'total_income' => (float) $reports->sum('total_income'),
            'total_maintenance' => (float) $reports->sum('maintenance_amount'),
            'total_owner_profit' => (float) $reports->sum('owner_profit'),
            'count' => $reports->count(),
        ];
    }

    /**
     * @return array{total_income:float, total_staff_cost:float, total_net_income:float, total_owner_share:float, total_partner_share:float, count:int}
     */
    public function partnershipSummary(Collection $reports): array
    {
        return [
            'total_income' => (float) $reports->sum('total_income'),
            'total_staff_cost' => (float) $reports->sum('staff_cost'),
            'total_net_income' => (float) $reports->sum('net_income'),
            'total_owner_share' => (float) $reports->sum('owner_share'),
            'total_partner_share' => (float) $reports->sum('partner_share'),
            'count' => $reports->count(),
        ];
    }
}
