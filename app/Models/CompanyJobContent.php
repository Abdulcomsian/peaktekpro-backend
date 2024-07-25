<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyJobContent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'company_job_content';

    public function images()
    {
        return $this->hasMany(CompanyJobContentMedia::class, 'content_id', 'id');
    }

}
