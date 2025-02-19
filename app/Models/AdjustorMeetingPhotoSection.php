<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustorMeetingPhotoSection extends Model
{
    use HasFactory;

    protected $fillable=[
        'adjustor_meeting_id',
        'front',
        'exteriorPhotos_front',
        'front_left',
        'exteriorPhotos_front_left',
        'left',
        'exteriorPhotos_left',
        'back_left',
        'exteriorPhotos_back_left',
        'back',
        'exteriorPhotos_back',
        'back_right',
        'exteriorPhotos_back_right',
        'right',
        'exteriorPhotos_right',
        'front_right',
        'exteriorPhotos_front_right'
    ];
}
