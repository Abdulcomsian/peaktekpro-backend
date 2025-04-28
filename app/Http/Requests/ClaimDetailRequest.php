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
            'insurance_company' => 'nullable',
            'desk_adjustor' => 'nullable',
            'email' => 'nullable',

            'claim_number' => 'nullable',
            'supplement_amount' => 'nullable|string',
            'status' => 'nullable|in:Pending,Supplement,Denied,Approved',
            'last_update_date' =>'nullable|date',
            'notes' => 'nullable|string',

            // 'pdf_path' => 'nullable',
            // 'file_name'=> 'nullable',


        ];
    }
}
