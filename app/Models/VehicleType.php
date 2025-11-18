<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $fillable = ['name'];
    
    public function vehicles() {
        return $this->hasMany(Vehicle::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}
