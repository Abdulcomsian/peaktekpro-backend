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
        'front_imagePath',
        'front_left',
        'front_left_imagePath',
        'left',
        'left_imagePath',
        'back_left',
        'back_left_imagePath',
        'back',
        'back_imagePath',
        'back_right',
        'back_right_imagePath',
        'right',
        'right_imagePath',
        'front_right',
        'front_right_imagePath'
    ];
}
