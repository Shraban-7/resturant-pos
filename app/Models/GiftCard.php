<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class GiftCard extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'gift_card_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }

    public function isRedeemable(): bool
    {
        return $this->is_active
            && $this->balance > 0
            && ($this->expiry_date === null || ! $this->expiry_date->isPast());
    }

    /**
     * Charge an amount against the card balance. Assumes a locked row.
     *
     * @throws RuntimeException when the card is invalid or has insufficient balance.
     */
    public function redeem(float $amount, bool $save = true): float
    {
        if (! $this->isRedeemable()) {
            throw new RuntimeException('Gift card is not valid.');
        }

        if ($amount <= 0) {
            return 0;
        }

        if ($this->balance < $amount) {
            throw new RuntimeException('Gift card balance is insufficient.');
        }

        $this->balance -= $amount;

        if ($save) {
            $this->save();
        }

        return $this->balance;
    }
}



