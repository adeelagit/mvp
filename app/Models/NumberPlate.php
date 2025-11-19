<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberPlate extends Model
{
    protected $fillable = [
        'plate_number',
        'image',
    ];
}
