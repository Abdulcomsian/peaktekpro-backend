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

    public function meetingPhotoSections()
    {
        return $this->hasMany(AdjustorMeetingPhotoSection::class, 'adjustor_meeting_id');
    }

    // Define the relationship with adjustor_square_photos
    public function squarePhotos()
    {
        return $this->hasMany(AdjustorSquarePhotos::class, 'adjustor_meeting_id');
    }

    public function isComplete() 
    {
        // Check if any of the columns are NULL
        $photoSections = $this->meetingPhotoSections;
        $squarePhotos = $this->squarePhotos;
        // Debug: Check if photoSections is empty
        if ($photoSections->isEmpty() || $squarePhotos->isEmpty()) {
            return false; 
        }

        // dd($photoSections->toArray());

        $missingPhotos = $photoSections->filter(function ($section) {
            return is_null($section->exteriorPhotos_front) ||
                is_null($section->exteriorPhotos_front_left) ||
                is_null($section->exteriorPhotos_left) ||
                is_null($section->exteriorPhotos_back_left) ||
                is_null($section->exteriorPhotos_back) ||
                is_null($section->exteriorPhotos_back_right) ||
                is_null($section->exteriorPhotos_right) ||
                is_null($section->exteriorPhotos_front_right);
        })->isNotEmpty();

        $missingSquarePhotos = $squarePhotos->filter(function ($section) {
            return is_null($section->square_photos);
        })->isNotEmpty();

        if($missingPhotos || $missingSquarePhotos)
        {
            return false;  // If any row is returned, it's incomplete (false), else complete (true)
        }

        return true;



            // return $missingPhotos ? false : true;  // If any row is returned, it's incomplete (false), else complete (true)

            // dd($missingPhotos);

        // If any column is null, it's incomplete, otherwise it's complete
        // return !$missingPhotos; // Returns true if all columns are filled (none are null), otherwise false
    }


    public function isCompleteeee()
    {
        // if(!is_null($this->street) && !is_null($this->city) && !is_null($this->state) && !is_null($this->zip_code)
        // // && !is_null($this->insurance) && !is_null($this->claim_number) && !is_null($this->policy_number)
        // //  && !is_null($this->company_signature)
        // && !is_null($this->company_printed_name) && !is_null($this->company_date) && !is_null($this->customer_signature) && !is_null($this->customer_printed_name)
        // && !is_null($this->customer_date)) {
        //     return true;
        // } else {
        //     return false;
        // }

        $meetingPhotosComplete = $this->meetingPhotoSections()
        // ->where(function ($query) {
            // $query->
                    ->whereNull('exteriorPhotos_front')
                  ->orWhereNull('exteriorPhotos_front_left')
                  ->orWhereNull('exteriorPhotos_left')
                  ->orWhereNull('exteriorPhotos_back_left')
                  ->orWhereNull('exteriorPhotos_back')
                  ->orWhereNull('exteriorPhotos_back_right')
                  ->orWhereNull('exteriorPhotos_right')
                  ->orWhereNull('exteriorPhotos_front_right')
                  ->exists();

    

        // $squarePhotosComplete = $this->squarePhotos()
        //     ->whereNull('required_field_1') // Replace 'required_field_1' with actual field names
        //     ->orWhereNull('required_field_2') // Add other required fields
        //     ->doesntExist();

        // return $meetingPhotosComplete && $squarePhotosComplete;
        return $meetingPhotosComplete;


    }

    public function isComplete1()
    {
        $meetingPhotosComplete = $this->meetingPhotoSections()
        ->whereNull('exteriorPhotos_front') 
        ->orWhereNull('exteriorPhotos_front_left')
        ->orWhereNull('exteriorPhotos_left')
        ->orWhereNull('exteriorPhotos_back_left')
        ->orWhereNull('exteriorPhotos_back')
        ->orWhereNull('exteriorPhotos_back_right')
        ->orWhereNull('exteriorPhotos_right')
        ->orWhereNull('exteriorPhotos_front_right')
        ->doesntExist();
        return $meetingPhotosComplete;

    }
}
