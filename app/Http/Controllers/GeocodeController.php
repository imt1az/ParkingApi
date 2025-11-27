<?php

namespace App\Http\Controllers;

use App\Services\Geocoder;
use Illuminate\Http\Request;

class GeocodeController extends Controller
{
    public function store(Request $r, Geocoder $geo)
    {
        $r->validate(['query' => 'required|string|min:3']);
        $result = $geo->search($r->input('query'));

        return response()->json($result);
    }
}
