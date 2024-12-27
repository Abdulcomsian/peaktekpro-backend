<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status'
    ];

    public function reportPages()
    {
        return $this->hasMany(ReportPage::class)->orderBy('order_no','asc');
    }

    public function getAllReportData()
    {
        return $this->load(['reportPages.pageData']);
    }
}
