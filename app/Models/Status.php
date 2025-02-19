<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    // public function jobs()
    // {
    //     return $this->hasMany(CompanyJob::class);
    // }

    public function tasks()
    {
        return $this->hasMany(CompanyJob::class);
    }

    public function jobSummaries()
    {
        return $this->hasManyThrough(CompanyJobSummary::class, CompanyJob::class, 'status_id', 'company_job_id', 'id', 'id');
    }


}
