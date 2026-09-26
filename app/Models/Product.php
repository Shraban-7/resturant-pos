<?php

namespace App\Models;

use App\Enums\MealSlot;
use App\Enums\ProductType;
use App\Traits\BelongsToBranch;
use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
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

    protected $appends = [
        'display_name',
        'image_url',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'meal_times' => AsEnumCollection::class.':'.MealSlot::class,
    ];

    public const MEAL_SLOTS = [
        MealSlot::BREAKFAST->value,
        MealSlot::LUNCH->value,
        MealSlot::DINNER->value,
    ];

    public static function types(): array
    {
        return ProductType::values();
    }

    /**
     * Buffet = fixed per-person price, unlimited seats (capacity is managed
     * by tables/reservations, not inventory). Quantity in cart = headcount.
     */
    public function isBuffet(): bool
    {
        return ($this->type ?? ProductType::DISH) === ProductType::BUFFET;
    }

    /** Raw material: usable in recipes, never sold directly. */
    public function isIngredient(): bool
    {
        return ($this->type ?? ProductType::DISH) === ProductType::INGREDIENT;
    }

    public function isDish(): bool
    {
        return ($this->type ?? ProductType::DISH) === ProductType::DISH;
    }

    /** Sellable menu items for POS (dishes + buffets only; raw excluded). */
    public function scopeSellable($query)
    {
        return $query->whereIn(
            $query->getModel()->getTable().'.type',
            [ProductType::DISH, ProductType::BUFFET]
        );
    }

    public function scopeRawIngredients($query)
    {
        return $query->where($query->getModel()->getTable().'.type', ProductType::INGREDIENT);
    }

    /**
     * Meal slots with serving hours (24h). Used to show
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

    /** Empty meal_times = served all day. */
    public function availableAt(string|MealSlot $slot): bool
    {
        $slot = $slot instanceof MealSlot ? $slot->value : $slot;

        if (! $this->meal_times || $this->meal_times->isEmpty()) {
            return true;
        }

        return $this->meal_times->contains(fn (MealSlot $m) => $m->value === $slot);
    }

    /**
     * Plain string values of meal_times (the cast returns a Collection,
     * which cannot go directly into in_array()).
     */
    public function mealTimeValues(): array
    {
        if (empty($this->meal_times)) {
            return [];
        }

        if ($this->meal_times instanceof \Illuminate\Support\Collection) {
            return $this->meal_times
                ->map(fn ($m) => $m instanceof MealSlot ? $m->value : (string) $m)
                ->values()
                ->all();
        }

        return array_values((array) $this->meal_times);
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

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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

    /**
     * Suggested extra items for this menu item (e.g. Burger → Coke).
     * Add-ons are real sellable products added as their own sale lines.
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_addons', 'product_id', 'addon_product_id')
            ->withTimestamps();
    }

    /**
     * Priced extras selectable for this dish in the POS modal
     * (e.g. Cheese, Extra Tomato on Burger).
     */
    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Addition::class, 'addition_product', 'product_id', 'addition_id')
            ->withTimestamps();
    }

    public function availableStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_in - $this->stock_out
        );
    }

    /**
     * Localized product name: Bangla when locale is `bn` and a
     * translation exists, otherwise the default (English) name.
     */
    public function displayName(): string
    {
        if (app()->getLocale() === 'bn' && ! empty($this->name_bn)) {
            return $this->name_bn;
        }

        return $this->name;
    }

    public function displayNameAttribute(): string
    {
        return $this->displayName();
    }

    /** Public image URL with a built-in default when no photo is uploaded. */
    public function imageUrl(): string
    {
        if (! empty($this->image)) {
            return asset('storage/'.$this->image);
        }

        return asset('assets/images/default-product.svg');
    }

    public function imageUrlAttribute(): string
    {
        return $this->imageUrl();
    }

    public function lastStockEntry()
    {
        return ProductStock::where('product_id', $this->id)
            ->latest('id')
            ->first();
    }
}

