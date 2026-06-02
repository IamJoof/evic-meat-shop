<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return auth()->user()?->is_admin === true;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $rules = [
            'image_path' => 'sometimes|image|mimes:jpg,jpeg,png|max:25000',
            'is_available' => 'sometimes|boolean',

        ];


        if($this->isMethod('put') || $this->isMethod('patch')){
            return array_merge($rules,[
                'name' => 'sometimes|string|max:100',
                'price_per_kg' => 'sometimes|numeric|min:0',
                'category_id' => 'sometimes|integer|exists:categories,id'
            ]);
        }

        return array_merge($rules,[
            'name' => 'required|string|max:100',
            'price_per_kg' => 'sometimes|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,id'
        ]);
    }
}
