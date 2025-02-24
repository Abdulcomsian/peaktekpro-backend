<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadyToBuildMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'ready_build_id',
        'image_url',
        'file_name'
    ];
}
