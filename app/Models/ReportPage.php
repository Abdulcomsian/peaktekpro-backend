<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'name',
        'slug',
        'is_active',
        'order_no'
    ];

    public function pageData()
    {
        return $this->hasOne(ReportPageData::class, 'report_page_id');
    }
}
