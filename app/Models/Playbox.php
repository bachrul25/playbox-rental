<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playbox extends Model
{
    protected $table = 'playboxes';

    protected $fillable = [
        'code',
        'name',
        'ownership_type',
        'partner_id',
        'location',
        'status',
        'condition_note',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'tersedia' => 'bg-success',
            'disewa' => 'bg-primary',
            'maintenance' => 'bg-warning text-dark',
            'tidak_aktif' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }
}
