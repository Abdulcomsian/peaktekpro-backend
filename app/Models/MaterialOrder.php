<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function deliveryInformation()
    {
        return $this->hasOne(MaterialOrderDeliveryInformation::class);
    }

    public function materials()
    {
        return $this->hasMany(MaterialOrderMaterial::class);
    }
}
