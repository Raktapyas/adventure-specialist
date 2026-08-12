<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\NormalizesMediaPath;
use App\Models\Page;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePageRequest extends FormRequest
{
    use NormalizesMediaPath;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeMediaPath('cover_image');

        if ($this->boolean('remove_parent')) {
            $this->merge(['parent_id' => null]);
        }

        if ($this->exists('is_published')) {
            $this->merge(['is_published' => $this->boolean('is_published')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:pages,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('pages', 'slug')->ignore($this->route('page'))],
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
            $page = $this->route('page');
            $parentId = $this->input('parent_id');

            if ($parentId === null || ! $page) {
                return;
            }

            if ((int) $parentId === (int) $page->id) {
                $validator->errors()->add('parent_id', 'A page cannot be its own parent.');
            }

            if (in_array((int) $parentId, $page->descendantIds(), true)) {
                $validator->errors()->add('parent_id', 'A page cannot be a descendant of itself.');
            }

            $parentDepth = $page->id === (int) $parentId ? 0 : (Page::find($parentId)?->chainDepth() ?? 0);

            if ($parentDepth >= 1) {
                $validator->errors()->add('parent_id', 'A page can only be nested directly under a top-level page.');
            }
        });
    }
}
