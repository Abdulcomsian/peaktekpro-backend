<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyJobSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_job_id',
        'job_total',
        'first_payment',
        'first_payment_cheque_number',
        'deductable',
        'deductable_cheque_number',
        'upgrades',
        'upgrades_cheque_number',
        'final_payment',
        'final_payment_cheque_number',
        'balance',
        'is_fully_paid',
        'full_payment_date',
        'invoice_number',
        'market',
        'lead_source',
        'insurance',
        'policy_number',
        'email',
        'insurance_representative',
        'claim_number',
        'job_type',
        'lead_status',
        'created_at',
        'updated_at',
        'customer_name',
        'status'
    ];
    protected $guarded = [];

    public function customerAggrement()
    {
        return $this->hasMany(CustomerAgreement::class, 'company_job_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(CompanyJob::class, 'company_job_id');
    }

    
}
