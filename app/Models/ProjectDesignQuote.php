<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDesignQuote extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sections()
    {
        return $this->hasMany(Section::class, 'quote_id', 'id');
    }
}
