<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildPacketChecklist extends Model
{
    use HasFactory;

    protected $fillable=[
        'company_job_id',
        'project_overview',
        'scope_of_work',
        'customer_preparation',
        'photo_documentation',
        'product_selection',
        'authorization',
        'terms_condition',
        'is_complete',
        'status'
    ];
}
