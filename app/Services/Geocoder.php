<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Simple wrapper around OpenStreetMap Nominatim.
 * Replace base URL or add API key logic if you switch providers.
 */
class Geocoder
{
    public function search(string $query): array
    {
        $resp = Http::withHeaders(['User-Agent' => 'ParkingApi/1.0'])
            ->withoutVerifying() // dev env: bypass local SSL trust issues
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'jsonv2',
                'limit' => 1,
            ]);

        if (! $resp->ok() || empty($resp[0])) {
            throw new RuntimeException('ঠিকানা থেকে অবস্থান পাওয়া যায়নি');
        }

        $item = $resp[0];
        return [
            'lat' => (float) $item['lat'],
            'lng' => (float) $item['lon'],
            'address' => $item['display_name'] ?? $query,
        ];
    }
}
