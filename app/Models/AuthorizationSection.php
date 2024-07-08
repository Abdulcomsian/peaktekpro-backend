<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorizationSection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function authorization()
    {
        return $this->belongsTo(ProjectDesignAuthorization::class);
    }

    public function items()
    {
        return $this->hasMany(AuthorizationItem::class, 'authorization_section_id', 'id');
    }
}
