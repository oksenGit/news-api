<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsFilterRequest extends FormRequest
{
    private const MAX_PER_PAGE = 100;
    private const DEFAULT_PER_PAGE = 15;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string',
            'author' => 'nullable|string',
            'source' => 'nullable|string',
            'source_name' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE
        ];
    }

    public function messages(): array
    {
        return [
            'categories.*.exists' => 'One or more selected categories are invalid.',
            'date_from.date' => 'The from date is not a valid date.',
            'date_to.date' => 'The to date is not a valid date.',
            'page.integer' => 'Page must be a valid number.',
            'page.min' => 'Page must be at least 1.',
            'per_page.integer' => 'Items per page must be a valid number.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed ' . self::MAX_PER_PAGE . '.'
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        $validated['page'] = $validated['page'] ?? 1;
        $validated['per_page'] = $validated['per_page'] ?? self::DEFAULT_PER_PAGE;

        return $validated;
    }
}
