<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use BelongsToBranch, HasFactory, HasCommonScopes;

    protected $guarded = ['id'];

    protected $casts = [
        'meal_times' => 'array',
    ];

    public const MEAL_SLOTS = ['breakfast', 'lunch', 'dinner'];

    public const TYPE_DISH = 'dish';
    public const TYPE_BUFFET = 'buffet';

    /**
     * Buffet = fixed per-person price, unlimited seats (capacity is managed
     * by tables/reservations, not inventory). Quantity in cart = headcount.
     */
    public function isBuffet(): bool
    {
        return ($this->type ?? self::TYPE_DISH) === self::TYPE_BUFFET;
    }

    /**
     * Meal slots with serving hours (24h). Used by the storefront to show
     * "available now" dishes: breakfast-only, lunch-only, dinner-only, or all-day.
     */
    public static function mealSlotHours(): array
    {
        return [
            'breakfast' => ['label' => 'Breakfast', 'from' => 5, 'to' => 11],
            'lunch' => ['label' => 'Lunch', 'from' => 11, 'to' => 17],
            'dinner' => ['label' => 'Dinner', 'from' => 17, 'to' => 23],
        ];
    }

    public static function currentMealSlot(?int $hour = null): ?string
    {
        $hour ??= (int) now()->format('G');

        foreach (static::mealSlotHours() as $slot => $range) {
            if ($hour >= $range['from'] && $hour < $range['to']) {
                return $slot;
            }
        }

        return null; // late night: everything shows
    }

    /** NULL meal_times = served all day. */
    public function availableAt(string $slot): bool
    {
        if (empty($this->meal_times)) {
            return true;
        }

        return in_array($slot, $this->meal_times, true);
    }

    public function scopeForMealSlot($query, ?string $slot)
    {
        if (! $slot) {
            return $query;
        }

        return $query->where(function ($q) use ($slot) {
            $table = $q->getModel()->getTable();
            $q->whereNull("{$table}.meal_times")
                ->orWhereJsonContains("{$table}.meal_times", $slot);
        });
    }

    /**
     * Branch-filtered products include chain-wide (branch_id NULL) items.
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = active_branch_id();

        if ($branchId) {
            return $query->where(function ($q) use ($branchId) {
                $q->where($query->getModel()->getTable().'.branch_id', $branchId)
                    ->orWhereNull($query->getModel()->getTable().'.branch_id');
            });
        }

        return $query;
    }

    public function scopeSeller($query)
    {
        return $query->where('seller_id', panel_owner_id());
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', panel_owner_id());
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class, 'product_id');
    }

    public function productModifiers(): HasMany
    {
        return $this->hasMany(ProductModifier::class, 'product_id');
    }

    public function modifiers(): BelongsToMany
    {
        return $this->belongsToMany(Modifier::class, 'product_modifiers', 'product_id', 'modifier_id')
            ->withPivot(['is_required', 'max_select'])
            ->withTimestamps();
    }

    public function availableStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_in - $this->stock_out
        );
    }

    public function lastStockEntry()
    {
        return ProductStock::where('product_id', $this->id)
            ->latest('id')
            ->first();
    }
}
