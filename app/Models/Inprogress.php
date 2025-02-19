<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inprogress extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    public function getStatusAttribute($value)
    {
        return $value == 0 ? false : true;
    }

    // public function media()
    // {
    //     return $this->hasMany(InprogressMedia::class,'')
    // }
}
