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
        return auth()->check() && auth()->user()->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if($this->isMethod('put') || $this->isMethod('patch')){
            return [
                'name'          => 'sometimes|string|max:100',
                'image_path'    => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
                'price_per_kg'  => 'sometimes|decimal:2',
                'is_available'  => 'sometimes|boolean'
            ];
        }

        return [
            'name'              => 'required|string|max:100',
            'image_path'        => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'price_per_kg'      => 'required|decimal:2',
            'is_available'      => 'required|boolean|'
        ];
    }
}
