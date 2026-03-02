<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'full_name' => 'required|string|max:255',
            // 'phone' => 'required|digits:10',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'required|string|max:255',

            'city' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|digits:6',
            'type' => 'required|in:home,office'
        ];

    }
}
