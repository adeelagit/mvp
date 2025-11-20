<?php

namespace App\Jobs;

use App\Models\ServiceTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessServiceTicketMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticketId;
    protected $filename;

    public function __construct($ticketId, $filename)
    {
        $this->ticketId = $ticketId;
        $this->filename = $filename;
    }

    public function handle()
    {
        $ticket = ServiceTicket::find($this->ticketId);
        if (!$ticket) return;

        $tempPath = 'temp/service_tickets/' . $this->filename;
        $finalPath = 'service_tickets/' . $this->filename;

        // Move file from temp to final storage
        if (Storage::disk('public')->exists($tempPath)) {
            Storage::disk('public')->move($tempPath, $finalPath);

            // Detect file type
            $extension = pathinfo($finalPath, PATHINFO_EXTENSION);
            $type = in_array($extension, ['mp4', 'mov']) ? 'video' : 'image';

            // Create DB record
            $ticket->media()->create([
                'file_path' => $finalPath,
                'type' => $type,
            ]);
        }
    }
}
