<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        $user = auth('api')->user();
        abort_unless($user, 401, 'Unauthenticated');

        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $user->last_lat = $data['lat'];
        $user->last_lng = $data['lng'];
        $user->last_located_at = now();
        $user->save();

        return response()->json([
            'ok' => true,
            'lat' => $user->last_lat,
            'lng' => $user->last_lng,
            'last_located_at' => $user->last_located_at,
        ]);
    }
}
