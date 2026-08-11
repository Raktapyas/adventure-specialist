<?php

namespace App\Http\Requests\Admin;

use App\Models\Destination;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDestinationRequest extends FormRequest
{
    /**
     * Normalize the publishing checkbox (absent when unchecked).
     */
    protected function prepareForValidation(): void
    {
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
            'parent_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('destinations', 'slug')->ignore($this->route('destination'))],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
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
            $destination = $this->route('destination');
            $parentId = $this->input('parent_id');

            if ($parentId === null || ! $destination) {
                return;
            }

            if ((int) $parentId === (int) $destination->id) {
                $validator->errors()->add('parent_id', 'A destination cannot be its own parent.');
            }

            if (in_array((int) $parentId, $destination->descendantIds(), true)) {
                $validator->errors()->add('parent_id', 'A destination cannot be a descendant of itself.');
            }

            $depth = Destination::find($parentId)?->chainDepth() ?? 0;

            if ($depth >= 2) {
                $validator->errors()->add('parent_id', 'Destinations can be nested no deeper than three levels.');
            }
        });
    }
}
