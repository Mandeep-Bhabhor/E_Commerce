<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartStoreRequest extends FormRequest
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

            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('status', 'active'),
            ],

            'color_id' => [
                'required',
                'integer',
                'exists:colors,id',
            ],

            'size_id' => [
                'required',
                'integer',
                'exists:sizes,id',
            ],

        ];
    }
}
