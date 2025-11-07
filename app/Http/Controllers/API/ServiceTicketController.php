<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceTicket;
use Illuminate\Support\Facades\Validator;

class ServiceTicketController extends Controller
{
    public function store(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'category' => 'required|in:Low Battery / Charging Help,Mechanical Issue,Battery Swap Needed,Flat Tyre,Tow / Pickup Required,Other',
            'other_text' => 'nullable|string|max:500',
            'media' => 'nullable|file|mimes:jpeg,jpg,png,mp4,mov|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $path = $file->store('service_tickets', 'public'); // storage/app/public/service_tickets
            $data['media_path'] = $path;
        }

        $ticket = $user->serviceTickets()->create($data);

        return response()->json([
            'message' => 'Service ticket created successfully',
            'ticket' => $ticket
        ], 201);
    }

}
