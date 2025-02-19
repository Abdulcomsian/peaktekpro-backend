<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOrderMaterial extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function materialOrder()
    {
        return $this->belongsTo(MaterialOrder::class, 'material_order_id');
    }

}
