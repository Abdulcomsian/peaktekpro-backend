<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoofComponentGeneric extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function pdfs()
    {
        return $this->hasMany(RoofComponentGenericMedia::class);
    }
}
