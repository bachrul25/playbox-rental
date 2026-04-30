<?php

namespace App\Repositories;

use App\Models\PartnershipReport;
use App\Models\Playbox;
use App\Models\PrivateReport;
use App\Models\Rental;
use App\Support\RentalCalculator;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RentalRepository
{
    public function paginate(?string $search = null, ?string $type = null, ?string $from = null, ?string $to = null, int $perPage = 10): LengthAwarePaginator
    {
        return Rental::query()
            ->with(['playbox', 'partner', 'user'])
            ->when($search, fn (Builder $q) => $q->where(function ($w) use ($search) {
                $w->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('playbox', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            }))
            ->when($type, fn (Builder $q) => $q->where('rental_type', $type))
            ->when($from, fn (Builder $q) => $q->whereDate('rental_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('rental_date', '<=', $to))
            ->orderByDesc('rental_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function recent(int $limit = 5)
    {
        return Rental::query()
            ->with(['playbox', 'partner'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Compute duration (hours) and total income for a rental.
     *
     * @return array{duration:float, total_income:float}
     */
    public function computeTotals(Carbon $start, Carbon $end, float $pricePerHour): array
    {
        $duration = max(0, $start->floatDiffInRealHours($end));
        $duration = round($duration, 2);
        $total = round($duration * $pricePerHour, 2);
        return ['duration' => $duration, 'total_income' => $total];
    }

    public function createWithReport(array $data): Rental
    {
        return DB::transaction(function () use ($data) {
            $start = Carbon::parse($data['start_time']);
            $end = Carbon::parse($data['end_time']);
            $totals = $this->computeTotals($start, $end, (float) $data['price_per_hour']);

            $rental = Rental::create([
                'invoice_number' => Rental::generateInvoiceNumber(),
                'playbox_id' => $data['playbox_id'],
                'partner_id' => $data['rental_type'] === 'kerjasama' ? $data['partner_id'] : null,
                'user_id' => $data['user_id'],
                'rental_type' => $data['rental_type'],
                'rental_date' => $data['rental_date'],
                'start_time' => $start,
                'end_time' => $end,
                'duration' => $totals['duration'],
                'price_per_hour' => $data['price_per_hour'],
                'total_income' => $totals['total_income'],
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'],
                'note' => $data['note'] ?? null,
            ]);

            $this->generateReport($rental);

            return $rental->fresh(['privateReport', 'partnershipReport', 'playbox', 'partner']);
        });
    }

    public function updateWithReport(Rental $rental, array $data): Rental
    {
        return DB::transaction(function () use ($rental, $data) {
            $start = Carbon::parse($data['start_time']);
            $end = Carbon::parse($data['end_time']);
            $totals = $this->computeTotals($start, $end, (float) $data['price_per_hour']);

            $rental->update([
                'playbox_id' => $data['playbox_id'],
                'partner_id' => $data['rental_type'] === 'kerjasama' ? $data['partner_id'] : null,
                'rental_type' => $data['rental_type'],
                'rental_date' => $data['rental_date'],
                'start_time' => $start,
                'end_time' => $end,
                'duration' => $totals['duration'],
                'price_per_hour' => $data['price_per_hour'],
                'total_income' => $totals['total_income'],
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'],
                'note' => $data['note'] ?? null,
            ]);

            $rental->privateReport()->delete();
            $rental->partnershipReport()->delete();
            $this->generateReport($rental->fresh());

            return $rental->fresh(['privateReport', 'partnershipReport', 'playbox', 'partner']);
        });
    }

    public function delete(Rental $rental): bool
    {
        return DB::transaction(function () use ($rental) {
            $rental->privateReport()->delete();
            $rental->partnershipReport()->delete();
            return (bool) $rental->delete();
        });
    }

    private function generateReport(Rental $rental): void
    {
        if ($rental->rental_type === 'pribadi') {
            $b = RentalCalculator::privateBreakdown((float) $rental->total_income);
            PrivateReport::create([
                'rental_id' => $rental->id,
                'total_income' => $rental->total_income,
                'maintenance_amount' => $b['maintenance'],
                'owner_profit' => $b['owner_profit'],
                'maintenance_percentage' => RentalCalculator::PRIVATE_MAINTENANCE_PCT,
                'owner_percentage' => RentalCalculator::PRIVATE_OWNER_PCT,
                'report_date' => $rental->rental_date,
            ]);
        } else {
            $b = RentalCalculator::partnershipBreakdown((float) $rental->total_income);
            PartnershipReport::create([
                'rental_id' => $rental->id,
                'partner_id' => $rental->partner_id,
                'total_income' => $rental->total_income,
                'staff_cost' => $b['staff_cost'],
                'net_income' => $b['net_income'],
                'owner_share' => $b['owner_share'],
                'partner_share' => $b['partner_share'],
                'share_percentage' => RentalCalculator::PARTNERSHIP_SHARE_PCT,
                'report_date' => $rental->rental_date,
            ]);
        }
    }

    public function availablePlayboxes()
    {
        return Playbox::query()
            ->whereIn('status', ['tersedia', 'disewa'])
            ->orderBy('code')
            ->get();
    }
}
