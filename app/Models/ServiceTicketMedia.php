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
        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($this->file_path, '/');   
    }
}
