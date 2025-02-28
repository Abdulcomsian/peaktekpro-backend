<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'job_id',
        'status',
        'file_path',
        'report_type',
    ];

    public $appends = [
        'pdf_path'
    ];

    public function reportPages()
    {
        return $this->hasMany(ReportPage::class)->orderBy('order_no','asc');
    }

    public function getAllReportData()
    {
        return $this->load(['reportPages.pageData']);
    }

    public function getPdfPathAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

}
