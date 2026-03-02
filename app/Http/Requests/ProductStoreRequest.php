<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            //
            'name' => ['required', 'unique:products,name'],
            'detail' => 'required',
            'images' => [
                'required',
                'array',
                'min:1',
            ],
            'price' => ['required', 'numeric', 'min:0'],
            // 'status' => ['required','active'],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png',
                'max:10000', // 2MB per image
            ],
            'sizes' => ['required', 'array', 'min:1'],
            'sizes.*' => ['integer', 'exists:sizes,id'],

            'colors' => ['required', 'array', 'min:1'],
            'colors.*' => ['integer', 'exists:colors,id'],

            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
