<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyJobSummary extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customerAggrement()
    {
        return $this->hasMany(CustomerAgreement::class, 'company_job_id', 'id');
    }
}
