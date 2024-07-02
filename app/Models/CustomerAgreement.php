<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAgreement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function job()
    {
        return $this->belongsTo(CompanyJob::class, 'company_job_id', 'id');
    }
}
