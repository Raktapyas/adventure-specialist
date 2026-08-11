<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'media' => ['required', 'array', 'min:1', 'max:10'],
            'media.*' => ['required', 'file', 'image', 'max:5120', Rule::dimensions()->maxWidth(20000)->maxHeight(20000)],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
