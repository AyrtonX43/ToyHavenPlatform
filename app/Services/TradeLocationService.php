<?php

namespace App\Services;

class TradeLocationService
{
    /**
     * Calculate distance in km between two points (Haversine formula).
     */
    public function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Get midpoint between two coordinates.
     */
    public function midpoint(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        return [
            'lat' => ($lat1 + $lat2) / 2,
            'lng' => ($lng1 + $lng2) / 2,
        ];
    }
}
