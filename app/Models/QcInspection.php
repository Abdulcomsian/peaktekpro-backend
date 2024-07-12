<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcInspection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function materials()
    {
        return $this->hasMany(QcInspectionMaterials::class);
    }
}
