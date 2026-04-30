<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    public function paginate(?string $search = null, ?string $role = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->with('partner')
            ->when($search, fn (Builder $q) => $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn (Builder $q) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
}
