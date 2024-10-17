<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function siteAdmin()
    {
        return $this->hasOne(User::class, 'company_id', 'id')->where('role_id', [1,2]);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'id'); 
    }




}
