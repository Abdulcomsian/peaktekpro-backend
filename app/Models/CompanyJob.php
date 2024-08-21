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

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
