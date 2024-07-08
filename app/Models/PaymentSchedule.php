<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function types()
    {
        return $this->hasMany(PaymentScheduleType::class);
    }
}
