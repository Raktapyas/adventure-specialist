<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalizesMediaPath;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryImageRequest extends FormRequest
{
    use NormalizesMediaPath;

    /**
     * Normalize the image reference to its host-relative form.
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeMediaPath('image_url');
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
            'image_url' => $this->mediaPathRules('image_url', nullable: false),
            'caption' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
