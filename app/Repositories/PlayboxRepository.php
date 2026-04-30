<?php

namespace App\Repositories;

use App\Models\Playbox;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PlayboxRepository
{
    public function paginate(?string $search = null, ?string $status = null, ?string $ownership = null, int $perPage = 10): LengthAwarePaginator
    {
        return Playbox::query()
            ->with('partner')
            ->when($search, fn (Builder $q) => $q->where(function ($w) use ($search) {
                $w->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            }))
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($ownership, fn (Builder $q) => $q->where('ownership_type', $ownership))
            ->orderBy('code')
            ->paginate($perPage);
    }

    public function create(array $data): Playbox
    {
        return Playbox::create($data);
    }

    public function update(Playbox $playbox, array $data): Playbox
    {
        $playbox->update($data);
        return $playbox->fresh();
    }

    public function delete(Playbox $playbox): bool
    {
        return (bool) $playbox->delete();
    }

    public function find(int $id): ?Playbox
    {
        return Playbox::find($id);
    }

    public function generateCode(): string
    {
        $prefix = 'PBX';
        $last = Playbox::where('code', 'like', $prefix . '%')->orderByDesc('id')->value('code');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
