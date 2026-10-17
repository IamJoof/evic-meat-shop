<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_address' => ['required', 'string', 'max:1000'],
            'fulfillment_type' => ['required', 'in:delivery,pickup'],
            'expected_delivery_date' => ['required', 'date', 'after:now', 'before:+30 days'],
            'payment_option' => ['required', 'in:COD,online'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
