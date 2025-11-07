<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Vehicle, VehicleType, Brand};
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        $user = auth('api')->user();

        if (!is_array($request->all())) {
            return response()->json(['error' => 'Payload must be an array of vehicles'], 400);
        }

        $vehiclesData = $request->all();
        $createdVehicles = [];
        $errors = [];

        foreach ($vehiclesData as $index => $vehicleData) {
            $validator = Validator::make($vehicleData, [
                'vehicle_type'  => 'required|string|max:100',
                'brand_name'    => 'required|string|max:100',
                'model_name'    => 'required|string|max:100',
                'license_plate' => 'required|string|max:50|unique:vehicles,license_plate',
                'color'         => 'nullable|string|max:50',
                'year'          => 'nullable|integer|min:2000|max:' . date('Y'),
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors();
                continue;
            }

            $validated = $validator->validated();

            $type = VehicleType::firstOrCreate(['name' => ucfirst(strtolower($request->vehicle_type))]);

            $brand = Brand::firstOrCreate(['name' => ucfirst(strtolower($request->brand_name))]);

            $vehicle = Vehicle::create([
                'user_id' => $user->id,
                'vehicle_type_id' => $type->id,
                'brand_id' => $brand->id,
                'model_name' => $validated['model_name'],
                'license_plate' => $validated['license_plate'],
                'vehicle_color' => $validated['color'] ?? null,
                'vehicle_year' => $validated['year'] ?? null
            ]);

            $createdVehicles[] = $vehicle->load(['type', 'brand']);
        }

        return response()->json([
            'message' => 'Vehicle(s) processed successfully',
            'created' => $createdVehicles,
            'errors' => $errors,
        ], 201);
    }

}
