<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTicketMedia extends Model
{
    protected $fillable = ['file_path', 'type'];

    public function ticket()
    {
        return $this->belongsTo(ServiceTicket::class);
    }

    protected $appends = ['full_url'];

    public function getFullUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
