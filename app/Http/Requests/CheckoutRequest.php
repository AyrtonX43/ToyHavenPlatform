<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_item_ids' => 'nullable|array',
            'cart_item_ids.*' => 'integer|exists:cart_items,id',
            'shipping_address' => 'required|string|max:500',
            'shipping_phone' => ['required', 'string', 'regex:/^\+63[0-9]{10}$/'],
            'shipping_city' => 'required|string|max:100',
            'shipping_province' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|string|in:card,gcash,paymaya',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_phone.regex' => 'Please enter a valid 10-digit Philippine phone number (e.g. 9123456789).',
        ];
    }
}
