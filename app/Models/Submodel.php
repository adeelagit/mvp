<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submodel extends Model
{
    protected $fillable = ['submodel_name','submodel_image','brand_id'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
