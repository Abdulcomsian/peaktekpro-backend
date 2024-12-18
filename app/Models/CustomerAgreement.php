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

    public function isComplete()
    {
        if(!is_null($this->street) || !is_null($this->city) || !is_null($this->state) || !is_null($this->zip_code)
        // && !is_null($this->insurance) && !is_null($this->claim_number) && !is_null($this->policy_number)
        //  && !is_null($this->company_signature)
        && !is_null($this->company_signature)  && !is_null($this->company_printed_name) && !is_null($this->company_date) && !is_null($this->customer_signature) && !is_null($this->customer_printed_name)
        && !is_null($this->customer_date)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getStatusAttribute($value)
    {
        return $value == 0 ? false : true;
    }

    public function jobSummary()
    {
        return $this->belongsTo(CompanyJobSummary::class,'company_job_id', 'id');
    }
}
