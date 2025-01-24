<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceUnderReviewPhotos extends Model
{
    use HasFactory;
    protected $fillable=[
        'insurance_under_reviews_id',
        'photo',
        'label'
    ];
}
