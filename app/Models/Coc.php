<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coc extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function getStatusAttribute($value)
    {
        return $value == 0 ? false : true;
    }
    
    public function getCocInsuranceEmailSentAttribute($value)
    {
        return $value == 0 ? false : true;
    }
}
