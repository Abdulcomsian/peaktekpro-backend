<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterailDropDown extends Model
{
    use HasFactory;
     protected $fillable = [
        'order_key',
        'material_name',
        'color',
        'pdf_url',
     ];
     protected $casts = [
        'color' => 'array', // Automatically convert JSON to an array
    ];
}
