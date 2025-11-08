<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use App\Models\SpaceAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $r)
    {
        $r->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'start_ts' => 'required|date',
            'end_ts'   => 'required|date|after:start_ts',
            'radius_m' => 'sometimes|integer|min:100|max:5000'
        ]);

        $lat = (float) $r->lat;
        $lng = (float) $r->lng;
        $radius = (int) ($r->radius_m ?? 1500);

        // ছোট bbox (প্রায় হিসাব) -> পরে exact distance filter
        // ~0.02 deg ≈ ~2.2km ঢাকায় (পর্যাপ্ত for first filter)
        $bboxDelta = 0.02;

        // Core query:
        // - Active spaces within bbox
        // - At least one active availability window overlapping requested interval
        // - Calculate distance using ST_Distance_Sphere with on-the-fly points
       $spaces = DB::table('parking_spaces as ps')
    ->selectRaw("
        ps.id, ps.title, ps.address, ps.lat, ps.lng, ps.capacity, ps.height_limit,
        ST_Distance_Sphere(
            ST_PointFromText(CONCAT('POINT(', ps.lng, ' ', ps.lat, ')')),
            ST_PointFromText(CONCAT('POINT(', ?, ' ', ?, ')'))
        ) AS distance_m
    ", [$lng, $lat]) // <<— bindings এখানেই আসবে
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
                'radius_m' => $radius
            ]
        ]);
    }
}
