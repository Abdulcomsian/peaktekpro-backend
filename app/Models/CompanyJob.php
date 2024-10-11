<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyJob extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class);
    }

    public function summary()
    {
        return $this->hasOne(CompanyJobSummary::class);
    }
    
    public function materialOrder()
    {
        return $this->hasOne(MaterialOrder::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function adjustorMeeting()
    {
        return $this->hasOne(AdjustorMeeting::class)->where('status', 'Approved');
    }

    public function wonAndClosed()
    {
        return $this->hasOne(ReadyToClose::class)->where('status', 'true');
    }
    
    public function title()
    {
        return $this->hasOne(ProjectDesignTitle::class);
    }
    
    public function introduction()
    {
        return $this->hasOne(ProjectDesignIntroduction::class);
    }
    
    public function inspections()
    {
        return $this->hasMany(ProjectDesignInspection::class);
    }
    
    public function quote()
    {
        return $this->hasOne(ProjectDesignQuote::class);
    }
    
    public function authorization()
    {
        return $this->hasOne(ProjectDesignAuthorization::class);
    }
    
    public function terms()
    {
        return $this->hasOne(TermAndCondition::class);
    }
    
    public function paymentSchedule()
    {
        return $this->hasOne(PaymentSchedule::class);
    }
    
    public function roofComponent()
    {
        return $this->hasOne(RoofComponentGeneric::class);
    }
    
    public function xactimateReport()
    {
        return $this->hasOne(XactimateReport::class);
    }
}
