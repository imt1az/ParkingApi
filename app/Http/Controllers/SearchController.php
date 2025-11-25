<?php

namespace App\Http\Controllers;

use App\Services\Geocoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $r, Geocoder $geo)
    {
        $r->validate([
            'query'    => 'sometimes|string|min:3',
            'lat'      => 'required_without:query|numeric|between:-90,90',
            'lng'      => 'required_without:query|numeric|between:-180,180',
            'start_ts' => 'required|date',
            'end_ts'   => 'required|date|after:start_ts',
            'radius_m' => 'sometimes|integer|min:100|max:5000'
        ]);

        if ($r->filled('query')) {
            $resolved = $geo->search($r->input('query'));
            $lat = $resolved['lat'];
            $lng = $resolved['lng'];
            $resolvedAddress = $resolved['address'];
        } else {
            $lat = (float) $r->lat;
            $lng = (float) $r->lng;
            $resolvedAddress = null;
        }

        $radius = (int) ($r->radius_m ?? 1500);
        // Convert meters to an approximate degree window; keep a minimum to avoid empty bbox.
        $bboxDelta = max($radius / 110000, 0.02);

        $spaces = DB::table('parking_spaces as ps')
            ->selectRaw("
                ps.id, ps.title, ps.address, ps.place_label, ps.lat, ps.lng, ps.capacity, ps.height_limit,
                ST_Distance_Sphere(
                    ST_PointFromText(CONCAT('POINT(', ps.lng, ' ', ps.lat, ')')),
                    ST_PointFromText(CONCAT('POINT(', ?, ' ', ?, ')'))
                ) AS distance_m
            ", [$lng, $lat])
            ->where('ps.is_active', 1)
            ->whereBetween('ps.lat', [$lat - $bboxDelta, $lat + $bboxDelta])
            ->whereBetween('ps.lng', [$lng - $bboxDelta, $lng + $bboxDelta])
            ->whereExists(function ($q) use ($r) {
                $q->from('space_availability as av')
                  ->whereColumn('av.space_id', 'ps.id')
                  ->where('av.is_active', 1)
                  ->where('av.start_ts', '<', $r->end_ts)
                  ->where('av.end_ts',   '>', $r->start_ts);
            })
            ->having('distance_m', '<=', $radius)
            ->orderBy('distance_m')
            ->limit(50)
            ->get();

        return response()->json([
            'count' => $spaces->count(),
            'items' => $spaces,
            'requested' => [
                'lat' => $lat, 'lng' => $lng,
                'start_ts' => $r->start_ts, 'end_ts' => $r->end_ts,
                'radius_m' => $radius,
                'resolved_address' => $resolvedAddress,
            ]
        ]);
    }
}
