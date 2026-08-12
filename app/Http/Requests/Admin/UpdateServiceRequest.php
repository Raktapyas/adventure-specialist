<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalizesMediaPath;
use App\Models\Service;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateServiceRequest extends FormRequest
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
            'parent_id' => ['nullable', 'integer', 'exists:services,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'cover_image' => $this->mediaPathRules('cover_image'),
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $service = $this->route('service');
            $parentId = $this->input('parent_id');

            if ($parentId === null || ! $service) {
                return;
            }

            if ((int) $parentId === (int) $service->id) {
                $validator->errors()->add('parent_id', 'A service cannot be its own parent.');
            }

            if (in_array((int) $parentId, $service->descendantIds(), true)) {
                $validator->errors()->add('parent_id', 'A service cannot be a descendant of itself.');
            }

            $depth = Service::find($parentId)?->chainDepth() ?? 0;

            if ($depth >= 1) {
                $validator->errors()->add('parent_id', 'Services can be nested no deeper than two levels.');
            }
        });
    }
}
