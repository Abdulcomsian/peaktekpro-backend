<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcInspectionMaterials extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getDamagedAttribute()
    {
        return $this->attributes['damaged'] != 0;
    }
}
