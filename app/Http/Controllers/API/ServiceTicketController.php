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
    public function index(Request $request)
    {
        $user = auth('api')->user();

        $tickets = $user->serviceTickets()
            ->with('media') 
            ->latest() // Order by created_at DESC
            ->paginate(15); 

        return response()->json([
            'status' => 'success',
            'tickets' => $tickets->items(),
            'pagination' => [
                'total' => $tickets->total(),
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
            ]
        ]);
    }

    public function delete($id)
    {
        $serviceTicket = ServiceTicket::find($id);
        if (!$serviceTicket) {
            return response()->json([
                'success' => false,
                'message' => 'Service Ticket not found or already deleted.'
            ], 404);
        }

        $serviceTicket->delete();
        return response()->json([
            'success' => true,
            'message' => 'Service Ticket deleted successfully.'
        ], 200);
    }

    public function store(Request $request)
    {
        /**
         * IMPORTANT: upload_max_filesize and post_max_size are PHP_INI_SYSTEM directives
         * They CANNOT be changed with ini_set() at runtime - they must be set before PHP starts.
         * 
         * For Railway hosting, you MUST set these via Railway Environment Variables:
         * - PHP_INI_UPLOAD_MAX_FILESIZE=100M
         * - PHP_INI_POST_MAX_SIZE=100M
         * 
         * These ini_set() calls below will NOT work for upload_max_filesize/post_max_size,
         * but we keep them for memory_limit and max_execution_time which CAN be changed.
         */
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');
        @ini_set('max_input_time', '300');

        $user = auth('api')->user();

        // Check if POST data was truncated (file too large)
        // This happens when post_max_size is exceeded BEFORE PHP processes the request
        if (empty($request->all()) && $request->method() === 'POST' && $request->header('Content-Length')) {
            $contentLength = (int) $request->header('Content-Length');
            $postMaxSize = $this->convertToBytes(ini_get('post_max_size'));
            $uploadMaxSize = ini_get('upload_max_filesize');
            
            return response()->json([
                'errors' => [
                    'media' => [
                        'The uploaded file exceeds the maximum allowed size. ' .
                        'Current PHP limits: post_max_size=' . ini_get('post_max_size') . ', upload_max_filesize=' . $uploadMaxSize . '. ' .
                        'Request size: ' . $this->formatBytes($contentLength) . '. ' .
                        'SOLUTION: Set Railway environment variables: PHP_INI_POST_MAX_SIZE=100M and PHP_INI_UPLOAD_MAX_FILESIZE=100M, then redeploy.'
                    ]
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

    /**
     * Convert PHP ini size string (e.g., "100M") to bytes
     */
    private function convertToBytes($size)
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
                // no break
            case 'm':
                $size *= 1024;
                // no break
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function storeTicket(Request $request)
    {
        $user = auth('api')->user(); // User from API guard

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
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
        // Upload media files (if exists)
        if ($request->has('media')) {
            foreach ($request->file('media') as $file) {
                $extension = $file->getClientOriginalExtension();
                $type = in_array($extension, ['mp4', 'mov']) ? 'video' : 'image';
                $path = $file->store("service_tickets/{$ticket->id}", 'public');
                // Store path in DB
                $ticket->media()->create([
                    'file_path' => $path,
                    'type' => $type,
                ]);
            }
        }

        return response()->json([
            'message' => 'Ticket created successfully!',
            'ticket' => $ticket->load('media')
        ], 201);
    }

    public function viewTicket($id){
        $tickets = ServiceTicket::find($id);

        if(!$tickets){
            return response()->json([
                'success' => false,
                'message' => 'Service Ticket not found or already deleted.'
            ], 404);
        }
        return response()->json([
            'message' => 'Ticket created successfully!',
            'ticket' => $tickets->load('media')
        ], 201);
    }

    public function updateTicketStatus(Request $request, $id)
    {
        // Validate request first
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved',
        ], [
            'status.required' => 'The status field is required.',
            'status.in' => 'Invalid status. Allowed values are: Open, In Progress, Resolved.',
        ]);

        $ticket = ServiceTicket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Service Ticket not found or already deleted.'
            ], 404);
        }

        // Save update
        $ticket->status = $validated['status'];
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated successfully!',
            'ticket' => $ticket->load('media')
        ], 200);
    }

}
