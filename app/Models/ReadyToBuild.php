<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadyToBuild extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function subContractor()
    {
        return $this->belongsTo(User::class, 'sub_contractor_id', 'id');
    }

    public function companyJob()
    {
        return $this->belongsTo(CompanyJob::class);
    }
    public function documents()
    {
        return $this->hasMany(ReadyToBuildMedia::class, 'ready_build_id', 'id');
    }

}
