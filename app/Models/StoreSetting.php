<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * All storefront knobs with defaults. Toggles are stored as '1'/'0'.
     */
    public static function defaults(): array
    {
        return [
            'announcement_enabled' => '0',
            'announcement_text' => '',
            'hero_title' => '',
            'hero_subtitle' => '',
            'opening_hours' => 'Every day · 11:00 AM – 11:00 PM',
            'show_popular' => '1',
            'show_branches' => '1',
            'show_reservation' => '1',
            'footer_note' => '',
        ];
    }

    public static function allFor(?int $ownerId = null): array
    {
        $ownerId ??= panel_owner_id();
        $stored = static::query()
            ->where('admin_id', $ownerId)
            ->pluck('value', 'key')
            ->all();

        return array_merge(static::defaults(), $stored);
    }

    public static function get(string $key, ?int $ownerId = null): ?string
    {
        return static::allFor($ownerId)[$key] ?? null;
    }

    public static function saveMany(array $values, ?int $ownerId = null): void
    {
        $ownerId ??= panel_owner_id();

        foreach (static::defaults() as $key => $default) {
            static::updateOrCreate(
                ['admin_id' => $ownerId, 'key' => $key],
                ['value' => $values[$key] ?? $default]
            );
        }
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }
}
