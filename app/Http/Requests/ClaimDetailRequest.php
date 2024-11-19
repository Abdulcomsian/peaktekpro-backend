<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            
            'claim_number' => 'nullable',
            'status' => 'nullable|in:Pending,Supplement,Denied,Approved',
            'supplement_amount' => 'nullable|string',
            'notes' => 'nullable|string',
            'last_update_date' =>'nullable|date'

        ];
    }
}
