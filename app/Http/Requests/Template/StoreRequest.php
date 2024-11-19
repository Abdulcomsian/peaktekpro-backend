<?php

namespace App\Http\Requests\Template;

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
            'title' => 'string|required|max:255',
        ];
    }

    public function formatErrors($errors)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation Error',
            'errors' => $errors,
        ], 422);
    }
}
