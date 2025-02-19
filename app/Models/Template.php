<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'company_id'
    ];

    public function templatePages()
    {
        return $this->hasMany(TemplatePage::class)->orderBy('order_no','asc');
    }

}
