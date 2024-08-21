<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function materials()
    {
        return $this->hasMany(MaterialOrderMaterial::class);
    }

    public function job()
    {
        return $this->belongsTo(CompanyJob::class, 'company_job_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id', 'id');
    }
}
