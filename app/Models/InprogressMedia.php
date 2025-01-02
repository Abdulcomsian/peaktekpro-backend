<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InprogressMedia extends Model
{
    use HasFactory;
    protected $fillable=[
        'company_job_id',
        'image_path',
        'category',
        'labels',
    ];
}
