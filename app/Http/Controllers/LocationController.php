<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
        ]);

        $location = Location::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Location saved successfully',
            'data' => $location,
        ]);
    }

    public function current(Request $request)
    {
        $location = Location::where('user_id', $request->user()->id)->first();
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not set'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $location,
        ]);
    }
}
