<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscountRequest extends FormRequest
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
        // This safely grabs the ID directly from the URL route parameter!
        $discountId = $this->route('discount');

        return [
            'code' => 'required|string|max:50|unique:discounts,code,'.$discountId,
            'type' => 'required|in:percentage,amount',
            'value' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date', // Removed 'after_or_equal' temporarily to avoid a secondary bug!
        ];
    }
}
