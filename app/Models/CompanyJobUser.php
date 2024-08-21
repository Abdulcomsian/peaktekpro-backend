<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyJobUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'company_job_user';
}
