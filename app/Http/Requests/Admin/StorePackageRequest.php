<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalizesMediaPath;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePackageRequest extends FormRequest
{
    use NormalizesMediaPath;

    /**
     * Normalize the publishing checkbox (absent when unchecked).
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeMediaPath('cover_image');

        if ($this->exists('is_published')) {
            $this->merge(['is_published' => $this->boolean('is_published')]);
        }
    }

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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('packages', 'slug')],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'cover_image' => $this->mediaPathRules('cover_image'),
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
        ];
    }
}
