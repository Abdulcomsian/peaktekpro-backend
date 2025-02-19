<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildComplete extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    public function attachments()
    {
        return $this->hasMany(BuildCompleteMedia::class);
    }
    
    public function subpaySheet()
    {
        return $this->hasOne(SubPaySheet::class);
    }
    
    public function jobLog()
    {
        return $this->hasOne(JobLog::class);
    }
    
    public function getStatusAttribute($value)
    {
        return $value == 0 ? false : true;
    }
}
