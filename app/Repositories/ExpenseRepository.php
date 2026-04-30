<?php

namespace App\Repositories;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ExpenseRepository
{
    public function paginate(?string $search = null, ?string $type = null, ?string $from = null, ?string $to = null, int $perPage = 10): LengthAwarePaginator
    {
        return Expense::query()
            ->with(['playbox', 'partner'])
            ->when($search, fn (Builder $q) => $q->where('description', 'like', "%{$search}%"))
            ->when($type, fn (Builder $q) => $q->where('type', $type))
            ->when($from, fn (Builder $q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('expense_date', '<=', $to))
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);
        return $expense->fresh();
    }

    public function delete(Expense $expense): bool
    {
        return (bool) $expense->delete();
    }

    public function totalByType(?string $from = null, ?string $to = null): array
    {
        return Expense::query()
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }
}
