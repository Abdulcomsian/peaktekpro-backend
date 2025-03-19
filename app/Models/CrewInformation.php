<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrewInformation extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'company_job_id',
        'build_date',
        'status',
        'crew_name',
        'content',
        'email',
    ];
}
