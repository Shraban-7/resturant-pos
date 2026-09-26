<?php

namespace App\Support;

use App\Models\BusinessSetting;

/**
 * Discount: when enabled in Settings, auto-applies to every POS order.
 * Types: percentage (% off subtotal) or flat (fixed ৳ off).
 * Rule: POS manual discount overrides the settings discount when entered (> 0).
 */
final class GlobalDiscount
{
    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FLAT = 'flat';

    /**
     * @return array{enabled: bool, rate: float, type: string}
     */
    public static function configFor(int $adminId): array
    {
        $settings = BusinessSetting::query()->where('user_id', $adminId)->first();

        return self::configFromSettings($settings);
    }

    /**
     * @return array{enabled: bool, rate: float, type: string}
     */
    public static function configFromSettings(?BusinessSetting $settings): array
    {
        $enabled = (bool) ($settings?->global_discount_enabled ?? false);
        $type = (string) ($settings?->global_discount_type ?? self::TYPE_PERCENTAGE);
        if (! in_array($type, [self::TYPE_PERCENTAGE, self::TYPE_FLAT], true)) {
            $type = self::TYPE_PERCENTAGE;
        }
        $rate = max(0, (float) ($settings?->global_discount_rate ?? 0));
        if ($type === self::TYPE_PERCENTAGE) {
            $rate = min(100, $rate);
        }

        if (! $enabled || $rate <= 0) {
            return ['enabled' => false, 'rate' => 0.0, 'type' => $type];
        }

        return ['enabled' => true, 'rate' => $rate, 'type' => $type];
    }

    public static function amount(float $subtotal, ?BusinessSetting $settings = null, ?array $config = null): float
    {
        if ($config === null) {
            $config = self::configFromSettings($settings);
        } else {
            $type = (string) ($config['type'] ?? self::TYPE_PERCENTAGE);
            if (! in_array($type, [self::TYPE_PERCENTAGE, self::TYPE_FLAT], true)) {
                $type = self::TYPE_PERCENTAGE;
            }
            $config = [
                'enabled' => (bool) ($config['enabled'] ?? false),
                'rate' => max(0, (float) ($config['rate'] ?? 0)),
                'type' => $type,
            ];
            if ($type === self::TYPE_PERCENTAGE) {
                $config['rate'] = min(100, $config['rate']);
            }
        }

        if (empty($config['enabled']) || $config['rate'] <= 0 || $subtotal <= 0) {
            return 0.0;
        }

        if ($config['type'] === self::TYPE_FLAT) {
            return round(min($subtotal, $config['rate']), 2);
        }

        return round($subtotal * $config['rate'] / 100, 2);
    }

    /**
     * Manual POS discount overrides the settings discount when entered;
     * otherwise the settings discount applies (if enabled). Capped at subtotal.
     */
    public static function totalDiscount(float $subtotal, float $manual, ?BusinessSetting $settings = null, ?array $config = null): float
    {
        $subtotal = max(0, $subtotal);
        $manual = max(0, $manual);

        if ($manual > 0) {
            return min($subtotal, $manual);
        }

        return min($subtotal, self::amount($subtotal, $settings, $config));
    }
}
