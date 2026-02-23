<?php

namespace App\Services;

/**
 * LBC Express (Philippines) shipping rate estimation.
 * Uses approximate domestic rates. Configure in config/services.php.
 */
class LbcShippingService
{
    /**
     * Zone definitions: provinces/cities mapped to base rate multiplier.
     * Metro Manila = 1.0, Luzon = 1.2, Visayas = 1.5, Mindanao = 1.8
     */
    protected array $zoneKeywords = [
        'metro_manila' => ['metro manila', 'manila', 'ncr', 'quezon city', 'caloocan', 'pasig', 'mandaluyong', 'makati', 'taguig', 'pasay', 'las piñas', 'parañaque', 'muntinlupa', 'marikina', 'pateros', 'valenzuela', 'malabon', 'navotas', 'san juan'],
        'luzon' => ['bulacan', 'laguna', 'cavite', 'rizal', 'batangas', 'pampanga', 'tarlac', 'nueva ecija', 'bataan', 'pangasinan', 'la union', 'ilocos', 'cagayan', 'bicol', 'quezon', 'aurora', 'abra', 'apayao', 'benguet', 'ifugao', 'kalinga', 'mountain province', 'nueva vizcaya', 'quirino'],
        'visayas' => ['cebu', 'iloilo', 'negros', 'bohol', 'leyte', 'samar', 'panay', 'bacolod', 'iloilo city', 'cebu city', 'tacloban'],
        'mindanao' => ['davao', 'cagayan de oro', 'general santos', 'zamoboanga', 'cotabato', 'bukidnon', 'misamis', 'surigao', 'agusan', 'sultan kudarat', 'lanao', 'basilan', 'sulu', 'tawi-tawi'],
    ];

    protected float $baseRate;

    protected float $perKgRate;

    protected float $zoneMultiplier = 1.0;

    public function __construct()
    {
        $this->baseRate = (float) config('services.lbc.base_rate', 99);
        $this->perKgRate = (float) config('services.lbc.per_kg_rate', 35);
    }

    /**
     * Estimate shipping fee in PHP based on province/city and package weight (kg).
     */
    public function estimate(string $province, string $city, float $weightKg = 1): float
    {
        $this->resolveZone($province, $city);
        $base = $this->baseRate * $this->zoneMultiplier;
        $weightCharge = max(0, ceil($weightKg) - 1) * $this->perKgRate;

        return round($base + $weightCharge, 2);
    }

    protected function resolveZone(string $province, string $city): string
    {
        $search = strtolower($province . ' ' . $city);

        foreach ($this->zoneKeywords as $zone => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($search, $kw)) {
                    $this->zoneMultiplier = match ($zone) {
                        'metro_manila' => 1.0,
                        'luzon' => 1.25,
                        'visayas' => 1.6,
                        'mindanao' => 1.9,
                        default => 1.0,
                    };
                    return $zone;
                }
            }
        }

        $this->zoneMultiplier = 1.4;
        return 'luzon';
    }

    /**
     * Calculate total weight from cart items (products with weight, default 0.5kg each).
     */
    public function calculateCartWeight($cartItems): float
    {
        $totalKg = 0;
        foreach ($cartItems as $item) {
            $product = $item->product;
            $weight = $product && $product->weight ? (float) $product->weight : 0.5;
            $totalKg += $weight * $item->quantity;
        }

        return max(0.5, round($totalKg, 2));
    }
}
