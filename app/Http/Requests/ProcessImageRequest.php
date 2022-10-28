<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessImageRequest extends FormRequest
{
    /**
     * Check if current user is authorized to make this request
     * 
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Before validation
     * 
     * @return void
     */
    protected function prepareForValidation()
    {
        if (!str_contains($this->image, 'data:image/png;base64')) {
            abort(422, 'Wrong image type. Image should be base64 png');
        }
    }

    /**
     * Validation rules
     * 
     * @return array
     */
    public function rules()
    {
        return [
            'min_level' => 'required|integer|min:0|max:100',
            'image' => 'required|string',
        ];
    }
}
