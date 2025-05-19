<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verdict extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_job_id',
        'status'
    ];
}
