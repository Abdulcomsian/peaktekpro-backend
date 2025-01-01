<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverheadPercentage extends Model
{
    use HasFactory;

    protected $fillable=[
        'overhead_percentage'
    ];
}
