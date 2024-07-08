<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDesignAuthorization extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sections()
    {
        return $this->hasMany(AuthorizationSection::class, 'authorization_id', 'id');
    }
}
