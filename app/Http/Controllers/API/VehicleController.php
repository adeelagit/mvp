<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Vehicle, VehicleType, Brand};
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = $request->user()->vehicles()->with(['brand', 'type'])->get();
        return response()->json($vehicles);
    }

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

            $type = VehicleType::firstOrCreate(['name' => ucfirst(strtolower($validated['vehicle_type']))]);

            $brand = Brand::firstOrCreate(['name' => ucfirst(strtolower($validated['brand_name']))]);

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

    /**
     * List all supported EV brands
     */
    public function getBrand()
    {
        $brands = Brand::select('id', 'name', 'logo')->orderBy('name')->get();

        return response()->json([
            'message' => 'Brand list fetched successfully',
            'brands' => $brands
        ]);
    }

    public function show(Vehicle $vehicle)
    {
        return response()->json([
            'success' => true,
            'data' => $vehicle
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'vehicle_type' => 'required|string',
            'brand_name' => 'required|string',
            'model_name' => 'required|string',
            'license_plate' => 'required|string|unique:vehicles,license_plate,' . $vehicle->id,
            'vehicle_color' => 'nullable|string',
            'vehicle_year' => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle
        ]);
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message'=> 'Vehicle deleted successfully'
        ]);
    }

    public function storeVehicleCategory(Request $request){
        
        foreach ($request->vehicle_categories as $category) {
            VehicleType::firstOrCreate([
                'name' => ucfirst(strtolower($category))
            ]);
        }

        return response()->json([
            'success' => true,
            'message'=> 'Vehicle category added successfully'
        ]);
    }

    public function getVehicleCategory(){
        $VehicleTypes = VehicleType::select('id', 'name')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'vehicle_categories'=> $VehicleTypes
        ]);

    }

}
