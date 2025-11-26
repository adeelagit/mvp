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
        Log::info("Job Processing: Ticket {$this->ticketId}, File {$this->filename}");

        $ticket = ServiceTicket::find($this->ticketId);
        if (!$ticket) {
            Log::error("Job Failed: Ticket {$this->ticketId} not found.");
            // We return here because retrying won't fix a missing ticket
            return;
        }

        $tempPath = 'temp/service_tickets/' . $this->filename;
        $finalPath = 'service_tickets/' . $this->filename;
        $disk = Storage::disk('public');

        // CHECK IF FILE EXISTS
        if (!$disk->exists($tempPath)) {
            $msg = "CRITICAL: Worker cannot find file at {$tempPath}. This usually means the Worker is on a different server/container than the Web uploader.";
            Log::error($msg);
            
            // THROWING AN EXCEPTION forces the job into 'failed_jobs' table
            throw new \Exception($msg);
        }

        // Proceed if file exists
        $disk->move($tempPath, $finalPath);

        $extension = pathinfo($finalPath, PATHINFO_EXTENSION);
        $type = in_array(strtolower($extension), ['mp4', 'mov']) ? 'video' : 'image';

        $ticket->media()->create([
            'file_path' => $finalPath,
            'type' => $type,
        ]);

        Log::info("Job Success: Media created for Ticket {$this->ticketId}");

    }
}
