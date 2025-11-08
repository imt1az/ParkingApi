<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpaceController extends Controller
{
    private function mustBeProviderOrAdmin()
    {
        $u = auth('api')->user();
        abort_unless($u && in_array($u->role, ['provider','admin']), 403, 'FORBIDDEN');
        return $u;
    }

    // Provider/Admin: create new space
    public function store(Request $req)
    {
        $u = $this->mustBeProviderOrAdmin();

        $req->validate([
            'title'        => 'required|string|min:3|max:120',
            'description'  => 'nullable|string|max:2000',
            'address'      => 'nullable|string|max:255',
            'lat'          => 'required|numeric|between:-90,90',
            'lng'          => 'required|numeric|between:-180,180',
            'capacity'     => 'nullable|integer|min:1',
            'height_limit' => 'nullable|numeric|min:0',
            'is_active'    => 'nullable|boolean',
        ]);

        $space = ParkingSpace::create([
            'provider_id' => $u->id,
            'title'       => $req->title,
            'description' => $req->description,
            'address'     => $req->address,
            'lat'         => $req->lat,
            'lng'         => $req->lng,
            'capacity'    => $req->capacity ?? 1,
            'height_limit'=> $req->height_limit,
            'is_active'   => $req->boolean('is_active', true),
        ]);

        // location POINT ট্রিগার দিয়ে সেট হবে (মাইগ্রেশনে trigger আছে)
        // তবুও নিশ্চিত করতে চাইলে lat/lng আপডেট করে save করলে trigger চলবেই।

        return response()->json([
            'id'          => $space->id,
            'provider_id' => $space->provider_id,
            'title'       => $space->title,
            'description' => $space->description,
            'address'     => $space->address,
            'lat'         => $space->lat,
            'lng'         => $space->lng,
            'capacity'    => $space->capacity,
            'height_limit'=> $space->height_limit,
            'is_active'   => $space->is_active,
            'photos'      => [], // no images in MVP
            'created_at'  => $space->created_at,
        ], 201);
    }

    // Provider: own spaces list
    public function mySpaces(Request $req)
    {
        $u = $this->mustBeProviderOrAdmin();
        $q = ParkingSpace::query();

        if ($u->role === 'provider') {
            $q->where('provider_id', $u->id);
        }
        if ($req->filled('active')) {
            $q->where('is_active', filter_var($req->active, FILTER_VALIDATE_BOOL));
        }

        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    // Public: show by id
    public function show($id)
    {
        $space = ParkingSpace::findOrFail($id);

        return response()->json([
            'id'          => $space->id,
            'title'       => $space->title,
            'description' => $space->description,
            'address'     => $space->address,
            'lat'         => $space->lat,
            'lng'         => $space->lng,
            'capacity'    => $space->capacity,
            'height_limit'=> $space->height_limit,
            'is_active'   => $space->is_active,
            'photos'      => [], // image disabled
            'provider'    => [
                'id'   => $space->provider_id,
                // চাইলে নাম দেখাতে User relation যোগ করে নিতে পারেন
            ]
        ]);
    }

    // Provider/Admin: update space (owner check)
    public function update($id, Request $req)
    {
        $u = $this->mustBeProviderOrAdmin();
        $space = ParkingSpace::findOrFail($id);

        if ($u->role === 'provider' && $space->provider_id !== $u->id) {
            abort(403, 'FORBIDDEN');
        }

        $req->validate([
            'title'        => 'sometimes|string|min:3|max:120',
            'description'  => 'sometimes|nullable|string|max:2000',
            'address'      => 'sometimes|nullable|string|max:255',
            'lat'          => 'sometimes|numeric|between:-90,90',
            'lng'          => 'sometimes|numeric|between:-180,180',
            'capacity'     => 'sometimes|integer|min:1',
            'height_limit' => 'sometimes|nullable|numeric|min:0',
            'is_active'    => 'sometimes|boolean',
        ]);

        $space->fill($req->only([
            'title','description','address','lat','lng','capacity','height_limit','is_active'
        ]));
        $space->save(); // lat/lng বদলালে BEFORE UPDATE trigger location আপডেট করবে

        return response()->json($space);
    }
}
