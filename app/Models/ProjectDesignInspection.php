<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDesignInspection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function attachments()
    {
        return $this->hasMany(ProjectDesignInspectionMedia::class, 'inspection_id','id');
    }
}
