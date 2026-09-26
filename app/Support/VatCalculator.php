<?php

namespace App\Support;

use App\Models\BusinessSetting;
use App\Models\Sale;

/**
 * Configurable VAT for POS orders.
 *
 * Modes (per admin, controlled in Settings):
 * - disabled:  no VAT (legacy behavior).
 * - exclusive: VAT sits outside product prices ("without product") and is
 *              added on top: payable = (subtotal - discount) + vat.
 * - inclusive: VAT lives inside product prices ("with product"): payable is
 *              unchanged, vat is the extracted portion shown on the bill.
 */
final class VatCalculator
{
    public const MODE_DISABLED = 'disabled';

    public const MODE_EXCLUSIVE = 'exclusive';

    public const MODE_INCLUSIVE = 'inclusive';

    public static function modes(): array
    {
        return [self::MODE_EXCLUSIVE, self::MODE_INCLUSIVE];
    }

    public static function settingsFor(int $adminId): ?BusinessSetting
    {
        return BusinessSetting::query()->where('user_id', $adminId)->first();
    }

    /**
     * @return array{mode: string, rate: float, vat: float, payable: float}
     */
    public static function calculate(float $subtotal, float $discount, ?BusinessSetting $settings): array
    {
        $rate = max(0, (float) ($settings?->vat_rate ?? 0));
        $enabled = (bool) ($settings?->vat_enabled ?? false);

        $mode = $enabled && $rate > 0
            ? (string) ($settings->vat_mode ?: self::MODE_EXCLUSIVE)
            : self::MODE_DISABLED;

        if (! in_array($mode, [self::MODE_EXCLUSIVE, self::MODE_INCLUSIVE], true)) {
            $mode = $mode === self::MODE_DISABLED ? $mode : self::MODE_EXCLUSIVE;
        }

        $net = max(0, $subtotal - $discount);

        if ($mode === self::MODE_EXCLUSIVE) {
            $vat = round($net * $rate / 100, 2);
            $payable = $net + $vat;
        } elseif ($mode === self::MODE_INCLUSIVE) {
            $vat = round($net * $rate / (100 + $rate), 2);
            $payable = $net;
        } else {
            $rate = 0.0;
            $vat = 0.0;
            $payable = $net;
        }

        return ['mode' => $mode, 'rate' => $rate, 'vat' => $vat, 'payable' => $payable];
    }

    /**
     * Recompute a sale's frozen VAT + payable/due from its subtotal/discount
     * using the owning admin's current settings. Call after mutating
     * subtotal (item add/remove/quantity change) and before save().
     */
    public static function refreshSale(Sale $sale): void
    {
        $calc = self::calculate(
            (float) $sale->subtotal,
            (float) $sale->discount,
            self::settingsFor((int) $sale->admin_id)
        );

        $sale->vat_mode = $calc['mode'];
        $sale->vat_rate = $calc['rate'];
        $sale->vat_amount = $calc['vat'];
        $sale->payable = $calc['payable'];
        $sale->due = $calc['payable'] - (float) $sale->paid;
    }
}
