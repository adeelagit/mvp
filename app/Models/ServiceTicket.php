<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTicket extends Model
{
     protected $fillable = [
        'user_id',
        'category',
        'other_text',
        'media_path',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(ServiceTicketMedia::class);
    }

    protected static function booted()
    {
        static::deleting(function ($serviceTicket) {
            $serviceTicket->media()->delete();
        });
    }
}
