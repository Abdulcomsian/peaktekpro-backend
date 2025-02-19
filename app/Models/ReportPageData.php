<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPageData extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_page_id',
        'json_data'
    ];

    public $appends = ['file_url'];

    public function getJsonDataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getFileUrlAttribute()
    {
        return asset('/storage');
    }
}
