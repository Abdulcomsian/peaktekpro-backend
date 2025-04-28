<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable=[
        'company_job_id',
        'payment_date',
        'payment_amount',
        'payment_type',
        'check_number',
        'pdf_path',
        'file_name'
    ];
}
