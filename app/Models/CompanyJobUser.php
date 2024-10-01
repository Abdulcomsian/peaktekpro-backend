<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyJobUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'company_job_user';
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function job()
    {
        return $this->belongsTo(CompanyJob::class);
    }
}
