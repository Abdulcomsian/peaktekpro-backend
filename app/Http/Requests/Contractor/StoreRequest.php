<?php

namespace App\Http\Requests\Contractor;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'company_name' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
            'file_path' => 'nullable|file',
            'subject' => 'nullable|string',
            'content' => 'nullable|string'
        ];
    }
}
