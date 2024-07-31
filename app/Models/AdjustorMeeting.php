<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustorMeeting extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function attachments()
    {
        return $this->hasMany(AdjustorMeetingMedia::class, 'adjustor_id', 'id')->where('media_type', 'Document');
    }

    public function images()
    {
        return $this->hasMany(AdjustorMeetingMedia::class, 'adjustor_id', 'id')->where('media_type', 'image');
    }
    
    public function manufacturerAttachments()
    {
        return $this->hasMany(AdjustorMeetingMedia::class, 'adjustor_id', 'id')->where('media_type', 'Manufacturer Document');
    }
}
