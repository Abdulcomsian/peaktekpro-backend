<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_job_id',
        'file_path',
        'labels',
        'created_at',
        'updated_at',
        'status'
    ];
}
