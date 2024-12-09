<?php

namespace App\Models;

use App\Models\SubOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    use HasFactory;

    public function subOptions()
    {
        return $this->hasMany(SubOption::class);
    }
}
