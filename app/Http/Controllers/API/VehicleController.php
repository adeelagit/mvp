<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Vehicle, VehicleType, Brand, NumberPlate};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
        $vehicleTypes = VehicleType::with(['brands.submodels'])->get();

        $response = [];

        foreach ($vehicleTypes as $type) {
            $response[strtolower($type->name)] = $type->brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'logo' => $brand->logo,
                    'submodels' => $brand->submodels->map(function ($sub) {
                        return [
                            'submodel_id' => $sub->id,
                            'submodel_name' => $sub->submodel_name,
                            'submodel_image' => $sub->submodel_image,
                        ];
                    })
                ];
            });
        }
        return response()->json($response);
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

    public function storeVehicleBrands(Request $request){
        // Validate the request
        $request->validate([
            '*.name' => 'required|string|max:255',
            '*.logo' => 'nullable|string',
            '*.vehicle_type_id' => 'required|exists:vehicle_types,id',
            '*.submodels' => 'nullable|array',
            '*.submodels.*.submodel_name' => 'required|string',
            '*.submodels.*.submodel_image' => 'nullable|string',
        ]);

        $brands = [];

        foreach ($request->all() as $item) {

            // Create brand
            $brand = Brand::create([
                'name' => $item['name'],
                'logo' => $item['logo'] ?? null,
                'vehicle_type_id' => $item['vehicle_type_id'],
            ]);

            // Create submodels if available
            if (!empty($item['submodels'])) {
                foreach ($item['submodels'] as $sub) {
                    $brand->submodels()->create([
                        'submodel_name' => $sub['submodel_name'],
                        'submodel_image' => $sub['submodel_image'] ?? null,
                    ]);
                }
            }
            $brands[] = $brand->load('submodels'); // Load submodels for response
        }

        return response()->json([
            'message' => 'Brands created successfully',
            'data' => $brands
        ]);
    }

    public function storeNumberPlate(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $imagePath = null; 
    
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('numberplates', 'public');
        }

        $data = NumberPlate::create([
            'plate_number' => $request->plate_number,
            'image' => $imagePath
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Number plate saved successfully',
            'data' => $data,
        ], 201);
    }

    public function updateNumberPlate(Request $request, $id)
    {
        $plate = NumberPlate::find($id);

        if (!$plate) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found',
            ], 404);
        }

        $request->validate([
            'plate_number' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        // Update plate number
        if ($request->has('plate_number')) {
            $plate->plate_number = $request->plate_number;
        }

        // Replace image
        if ($request->hasFile('image')) {

            // delete old image
            if ($plate->image && \Storage::disk('public')->exists($plate->image)) {
                \Storage::disk('public')->delete($plate->image);
            }

            // upload new image
            $plate->image = $request->file('image')->store('numberplates', 'public');
        }

        $plate->update();

        return response()->json([
            'status' => true,
            'message' => 'Number plate updated successfully',
            'data' => $plate,
            'image_url' => $plate->image ? asset('storage/'.$plate->image) : null,
        ], 200);
    }

    public function destroyNumberPlate($id)
    {
        $plate = NumberPlate::find($id);

        if (!$plate) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found',
            ], 404);
        }

        // delete stored image
        if ($plate->image && \Storage::disk('public')->exists($plate->image)) {
            \Storage::disk('public')->delete($plate->image);
        }

        $plate->delete();

        return response()->json([
            'status' => true,
            'message' => 'Number plate deleted successfully',
        ], 200);
    }

    public function getNumberPlates(){
        $number_plates = NumberPlate::latest()->get();
        return response()->json([
            'status' => true,
            'number_plates' => $number_plates
        ], 200);
    }

    public function deleteBrand($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'status' => false,
                'message' => 'Brand not found',
            ], 404);
        }

        $brand->delete();

        return response()->json([
            'status' => true,
            'message' => 'Brand and its submodels deleted successfully',
        ]);
    }
}
