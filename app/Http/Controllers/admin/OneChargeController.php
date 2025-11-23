<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OneChargeController extends Controller
{
    // Render the View
    public function index()
    {
        return view('admin.dashboard');
    }

    // Fetch all data at once to populate the JS Store
    public function getAllData()
    {
        return response()->json([
            'users' => DB::table('app_users')->select('id', 'name', 'email', 'phone', 'role', 'joined_date as joined')->get(),
            'types' => DB::table('vehicle_types')->get(),
            'brands' => DB::table('brands')->get()->map(function($b) {
                $b->models = json_decode($b->models);
                return $b;
            }),
            'vehicles' => DB::table('vehicles')
                ->join('vehicle_types', 'vehicles.type_id', '=', 'vehicle_types.id')
                ->select('vehicles.*', 'vehicles.owner_id as ownerId', 'vehicles.type_id as typeId', 'vehicles.brand_id as brandId', 'vehicle_types.name as type')
                ->get(),
            'tickets' => DB::table('tickets')
                ->select('id', 'user_id as userId', 'category', 'description as desc', 'status', 'lat', 'lng', 'created_at as date')
                ->get(),
            'plates' => DB::table('plates')->select('id', 'number', 'image_url as img')->get(),
        ]);
    }

    // --- CRUD Operations ---

    public function storeUser(Request $request)
    {
        $id = DB::table('app_users')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'User',
            'joined_date' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['success' => true]);
    }

    public function storeType(Request $request)
    {
        DB::table('vehicle_types')->insert([
            'name' => $request->name,
            'icon' => 'fa-circle', // Default icon
            'created_at' => now()
        ]);
        return response()->json(['success' => true]);
    }

    public function storeBrand(Request $request)
    {
        // Handle file upload if needed, for now we use placeholder if empty
        $logo = $request->logo ?? 'https://via.placeholder.com/50';
        
        DB::table('brands')->insert([
            'name' => $request->name,
            'type_id' => $request->typeId,
            'logo' => $logo,
            'models' => json_encode($request->models),
            'created_at' => now()
        ]);
        return response()->json(['success' => true]);
    }

    public function storeVehicle(Request $request)
    {
        $batch = $request->all();
        // The frontend sends an array of vehicles
        foreach($batch as $v) {
            DB::table('vehicles')->insert([
                'owner_id' => $v['ownerId'],
                'type_id' => $v['typeId'],
                'brand_id' => $v['brandId'],
                'model' => $v['model'],
                'plate' => $v['plate'],
                'color' => $v['color'],
                'year' => $v['year'],
                'created_at' => now()
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function storePlate(Request $request)
    {
        DB::table('plates')->insert([
            'number' => $request->number,
            'image_url' => $request->img,
            'created_at' => now()
        ]);
        return response()->json(['success' => true]);
    }

    public function updateTicket(Request $request)
    {
        DB::table('tickets')->where('id', $request->id)->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);
        return response()->json(['success' => true]);
    }

    // // --- Deletion Handlers ---
    public function deleteUser($id) { DB::table('app_users')->delete($id); return response()->json(['success'=>true]); }
    public function deleteType($id) { DB::table('vehicle_types')->delete($id); return response()->json(['success'=>true]); }
    public function deleteBrand($id) { DB::table('brands')->delete($id); return response()->json(['success'=>true]); }
    public function deleteVehicle($id) { DB::table('vehicles')->delete($id); return response()->json(['success'=>true]); }
    public function deletePlate($id) { DB::table('plates')->delete($id); return response()->json(['success'=>true]); }
}
