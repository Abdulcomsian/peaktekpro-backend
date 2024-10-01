<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubPaySheet extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    public function attachments()
    {
        return $this->hasMany(SubPaySheetMedia::class);
    }
}
