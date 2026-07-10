<?php

namespace App\Http\Requests\ContentConnector;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentConnectorPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locales = array_keys(config('blog.locales', []));

        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'locale' => ['required', 'string', Rule::in($locales)],
            'category_id' => ['required', 'string', Rule::exists('blog_categories', 'id')],
            'faq_schema' => ['required', 'array'],
            'faq_schema.@type' => ['required', 'string', Rule::in(['FAQPage'])],
            'faq_schema.mainEntity' => ['required', 'array', 'min:1'],
            'faq_schema.mainEntity.*.@type' => ['required', 'string', Rule::in(['Question'])],
            'faq_schema.mainEntity.*.name' => ['required', 'string', 'max:500'],
            'faq_schema.mainEntity.*.acceptedAnswer' => ['required', 'array'],
            'faq_schema.mainEntity.*.acceptedAnswer.@type' => ['required', 'string', Rule::in(['Answer'])],
            'faq_schema.mainEntity.*.acceptedAnswer.text' => ['required', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'meta.title' => ['nullable', 'string', 'max:255'],
            'meta.meta_title' => ['nullable', 'string', 'max:255'],
            'meta.description' => ['nullable', 'string', 'max:500'],
            'meta.meta_description' => ['nullable', 'string', 'max:500'],
            'meta.excerpt' => ['nullable', 'string', 'max:1000'],
            'meta.group_id' => ['nullable', 'string', 'max:191'],
            'meta.post_id' => ['nullable', 'string', 'max:191'],
            'meta.article_id' => ['nullable', 'string', 'max:191'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category_id.required' => 'category_id es obligatorio.',
            'category_id.exists' => 'category_id no corresponde a ninguna categoría del blog.',
            'faq_schema.required' => 'faq_schema es obligatorio.',
            'faq_schema.mainEntity.min' => 'faq_schema.mainEntity debe incluir al menos una pregunta.',
        ];
    }
}
