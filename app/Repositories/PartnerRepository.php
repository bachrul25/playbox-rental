<?php

namespace App\Repositories;

use App\Models\Partner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PartnerRepository
{
    public function paginate(?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return Partner::query()
            ->withCount('playboxes')
            ->when($search, fn (Builder $q) => $q->where(function ($w) use ($search) {
                $w->where('cafe_name', 'like', "%{$search}%")
                  ->orWhere('person_in_charge', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->orderBy('cafe_name')
            ->paginate($perPage);
    }

    public function options(): Collection
    {
        return Partner::query()->orderBy('cafe_name')->get(['id', 'cafe_name']);
    }

    public function create(array $data): Partner
    {
        return Partner::create($data);
    }

    public function update(Partner $partner, array $data): Partner
    {
        $partner->update($data);
        return $partner->fresh();
    }

    public function delete(Partner $partner): bool
    {
        return (bool) $partner->delete();
    }
}
