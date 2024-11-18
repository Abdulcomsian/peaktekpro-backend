<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplatePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'name',
        'slug',
        'is_active',
        'order_no'
    ];

}
