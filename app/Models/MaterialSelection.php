<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialSelection extends Model
{
    use HasFactory;
    protected $fillable = [
        'material_order_id',
        'name',
        'option',
        'unit',
        'unit_cost',
        'quantity',
        'total'
    ];
}
