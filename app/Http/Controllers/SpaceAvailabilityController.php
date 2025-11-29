<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use App\Models\SpaceAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpaceAvailabilityController extends Controller
{
    private function mustBeProviderOrAdmin()
    {
        $u = auth('api')->user();
        abort_unless($u && in_array($u->role, ['provider','admin']), 403, 'FORBIDDEN');
        return $u;
    }

    // Public: list active availability windows for a space
    public function index($spaceId)
    {
        $space = ParkingSpace::findOrFail($spaceId);

        $rows = SpaceAvailability::where('space_id', $space->id)
            ->where('is_active', true)
            ->where('end_ts', '>', now())
            ->orderBy('start_ts')
            ->get([
                'id', 'space_id', 'start_ts', 'end_ts', 'base_price_per_hour', 'is_active'
            ]);

        return response()->json([
            'count' => $rows->count(),
            'items' => $rows,
        ]);
    }

    public function store($spaceId, Request $r)
    {



        $u = $this->mustBeProviderOrAdmin();

        $r->validate([
            'start_ts' => 'required|date',
            'end_ts'   => 'required|date|after:start_ts',
            'base_price_per_hour' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        $space = ParkingSpace::findOrFail($spaceId);
        if ($u->role === 'provider' && $space->provider_id !== $u->id) {
            abort(403, 'FORBIDDEN');
        }

        // Overlap check: requested [start,end) must not overlap any active availability
        $overlap = SpaceAvailability::where('space_id', $space->id)
            ->where('is_active', true)
            ->where(function ($q) use ($r) {
                $q->where(function ($qq) use ($r) {
                    $qq->where('start_ts', '<', $r->end_ts)
                       ->where('end_ts',   '>', $r->start_ts);
                });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'error' => ['code' => 'OVERLAP', 'message' => 'Availability overlaps existing window']
            ], 409);
        }

        $av = SpaceAvailability::create([
            'space_id' => $space->id,
            'start_ts' => $r->start_ts,
            'end_ts'   => $r->end_ts,
            'base_price_per_hour' => $r->base_price_per_hour,
            'is_active' => $r->boolean('is_active', true),
        ]);

        return response()->json($av, 201);
    }
}
