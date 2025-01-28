<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceUnderReview extends Model
{
    use HasFactory;
    protected $fillable=[
        'company_job_id',
        'notes',
        'status',
    ];

    public function getPhotos()
    {
        return $this->hasMany(InsuranceUnderReviewPhotos::class,'insurance_under_reviews_id','id');
    }
}
