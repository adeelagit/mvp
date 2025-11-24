<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ServiceTicket, ServiceTicketMedia};
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessServiceTicketMedia;


class ServiceTicketController extends Controller
{
    public function store(Request $request)
    {
        $user = auth('api')->user();

        // Check if POST data was truncated (file too large)
        if (empty($request->all()) && $request->method() === 'POST') {
            return response()->json([
                'errors' => [
                    'media' => ['The uploaded file exceeds the maximum allowed size. Maximum file size is 100MB.']
                ]
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'category' => 'required|in:Low Battery / Charging Help,Mechanical Issue,Battery Swap Needed,Flat Tyre,Tow / Pickup Required,Other',
            'other_text' => 'nullable|string|max:500',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpeg,jpg,png,mp4,mov|max:102400', // 100MB (in kilobytes)
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Create ticket first
        $ticket = $user->serviceTickets()->create($data);

        // Handle media
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                // Save temporarily
                $file->storeAs('temp/service_tickets', $filename, 'public');

                // Dispatch job to move and create DB
                ProcessServiceTicketMedia::dispatch($ticket->id, $filename);
            }
        }

        // Load ticket with media (may be empty at first)
        $ticket->load('media');

        return response()->json([
            'message' => 'Service ticket created successfully. Media will be processed shortly.',
            'ticket' => $ticket
        ], 201);
    }

}
