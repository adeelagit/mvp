<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Vehicle, VehicleType, Brand, NumberPlate};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                'license_plate' => 'nullable|string|max:50',
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
                    'vehicle_type_id' => $brand->vehicle_type_id,
                    'logo' => $brand->logo ? asset('storage/' . $brand->logo) : null,
                    'submodels' => $brand->submodels->map(function ($sub) {
                        return [
                            'submodel_id' => $sub->id,
                            'submodel_name' => $sub->submodel_name,
                            'submodel_image' => $sub->submodel_image ? asset('storage/' . $sub->submodel_image) : null,
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
            'license_plate' => 'string|unique:vehicles,license_plate,' . $vehicle->id,
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
            '*.logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            '*.vehicle_type_id' => 'required|exists:vehicle_types,id',
            '*.submodels' => 'nullable|array',
            '*.submodels.*.submodel_name' => 'required|string',
            '*.submodels.*.submodel_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $brands = [];

        foreach ($request->all() as $index => $item) {
            /** ---- SAVE LOGO INTO /storage/app/public/brands ---- **/
            $logoPath = null;
            if ($request->file("$index.logo")) {
                $logoPath = $request->file("$index.logo")->store('brands', 'public');
            }

            // Create brand
            $brand = Brand::create([
                'name' => $item['name'],
                'logo' => $logoPath,
                'vehicle_type_id' => $item['vehicle_type_id'],
            ]);

            // Create submodels if available
            if (!empty($item['submodels'])) {
                foreach ($item['submodels'] as $sIndex => $sub) {
                    
                    // Save submodel image
                    $subImgPath = null;
                    if ($request->file("$index.submodels.$sIndex.submodel_image")) {
                        $subImgPath = $request->file("$index.submodels.$sIndex.submodel_image")
                                          ->store('submodels', 'public');
                    }

                    $brand->submodels()->create([
                        'submodel_name' => $sub['submodel_name'],
                        'submodel_image' => $subImgPath,
                    ]);
                }
            }
            $brands[] = $brand->load('submodels');
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

    public function updateBrand(Request $request, Brand $brand)
    {
        // 1. Validate the incoming single brand payload
        $request->validate([
            'name' => 'nullable|string|max:255',
            // logo is nullable, but if present, must be an image
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', 
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            
            'submodels' => 'nullable|array',
            // Submodel ID is nullable (for new submodels) or required (for existing ones)
            'submodels.*.id' => 'nullable|integer|exists:submodels,id', 
            'submodels.*.submodel_name' => 'required|string',
            'submodels.*.submodel_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        DB::beginTransaction();

        try {
            // --- 2. Handle Brand Logo Update ---
            $logoPath = $brand->logo;

            if ($request->file('logo')) {
                // Delete the old logo if it exists
                if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                    Storage::disk('public')->delete($brand->logo);
                }
                // Store the new logo
                $logoPath = $request->file('logo')->store('brands', 'public');
            }

            // Update Brand details
            $brand->update([
                'name' => $request->name,
                'logo' => $logoPath,
                'vehicle_type_id' => $request->vehicle_type_id,
            ]);

            // --- 3. Handle Submodels (Create, Update, and Sync) ---
            $submodelIdsToKeep = [];
            $submodelsInput = $request->input('submodels', []); // Get submodels or empty array

            foreach ($submodelsInput as $sIndex => $sub) {
                
                $submodelData = ['submodel_name' => $sub['submodel_name']];
                $subImgPath = null;
                
                // Determine if file upload key is present for this submodel
                $submodelImageFile = $request->file("submodels.$sIndex.submodel_image");
                
                if (isset($sub['id'])) {
                    // UPDATE existing submodel
                    // Ensure the submodel belongs to the current brand
                    $submodel = $brand->submodels()->findOrFail($sub['id']); 
                    $subImgPath = $submodel->submodel_image; // Default: keep old image
                    
                    if ($submodelImageFile) {
                        // New image uploaded: delete old one and store new
                        if ($submodel->submodel_image && Storage::disk('public')->exists($submodel->submodel_image)) {
                            Storage::disk('public')->delete($submodel->submodel_image);
                        }
                        $subImgPath = $submodelImageFile->store('submodels', 'public');
                    }

                    $submodelData['submodel_image'] = $subImgPath;
                    $submodel->update($submodelData);
                    $submodelIdsToKeep[] = $submodel->id; // Keep this ID
                } else {
                    // CREATE new submodel
                    if ($submodelImageFile) {
                        $subImgPath = $submodelImageFile->store('submodels', 'public');
                    }

                    $submodelData['submodel_image'] = $subImgPath;
                    $newSubmodel = $brand->submodels()->create($submodelData);
                    $submodelIdsToKeep[] = $newSubmodel->id; // Keep this new ID
                }
            }
            
            DB::commit();

            return response()->json([
                'message' => 'Brand updated successfully',
                'data' => $brand->load('submodels')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception for debugging
            Log::error("Brand update failed for ID: {$brand->id}. Error: " . $e->getMessage()); 
            
            return response()->json([
                'message' => 'An error occurred during the brand update process.',
                'error' => 'Internal Server Error. Please check logs for details.'
            ], 500);
        }
       
    }

    public function deleteVehicleCategory($id)
    {
        $vehicleType = VehicleType::find($id);
        if (!$vehicleType) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle category not found or already deleted.'
            ], 404);
        }

        $vehicleType->delete();
        return response()->json([
            'success' => true,
            'message' => 'Vehicle category deleted successfully.'
        ], 200);
    }

    public function updateVehicleCategory(Request $request, string $id)
    {
        // 1. Validation: Ensure the new name is provided and is a valid string.
        $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_types,name,' . $id,
        ]);

        // 2. Attempt to find the VehicleType model by its primary key (ID).
        $vehicleType = VehicleType::find($id);

        // 3. Check if the category exists. If not found, return a 404 Not Found response.
        if (!$vehicleType) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle category not found.'
            ], 404);
        }

        // 4. Update the name attribute, applying the same standardization as the store method.
        // The unique validation in step 1 ensures the new name is not a duplicate, 
        // while ignoring the current record's ID.
        $vehicleType->name = ucfirst(strtolower($request->input('name')));

        // 5. Save the changes to the database.
        $vehicleType->save();

        // 6. Return a successful response, including the updated resource.
        return response()->json([
            'success' => true,
            'message' => 'Vehicle category updated successfully.',
            'category' => $vehicleType
        ], 200);
    }
}
