<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplatePageData extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_page_id',
        'json_data'
    ];

    public function getJsonDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
