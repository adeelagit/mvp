<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name','logo','vehicle_type_id'];
    
    public function vehicles() {
        return $this->hasMany(Vehicle::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function submodels()
    {
        return $this->hasMany(Submodel::class);
    }

     // Automatically delete submodels when a brand is deleted
    protected static function booted()
    {
        static::deleting(function ($brand) {
            $brand->submodels()->delete();
        });
    }
}
