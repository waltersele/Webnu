<?php

namespace App\Http\Requests\ContentConnector;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ContentConnectorPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locales = array_keys(config('blog.locales', []));
        $allowedMimes = config('blog.featured_image.allowed_mimes', []);

        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'locale' => ['required', 'string', Rule::in($locales)],
            'status' => ['required', 'string', Rule::in(['published', 'scheduled'])],
            'published_at' => ['required', 'date'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'focus_keyword' => ['nullable', 'string', 'max:191'],
            'category_id' => ['nullable', 'string', Rule::exists('blog_categories', 'id')],
            'featured_image_url' => ['nullable', 'string', 'max:2048', 'url'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'featured_image_base64' => ['nullable', 'string'],
            'featured_image_mime' => ['nullable', 'string', 'max:64', Rule::in($allowedMimes)],
            'faq_schema' => ['nullable', 'array'],
            'faq_schema.@type' => ['required_with:faq_schema', 'string', Rule::in(['FAQPage'])],
            'faq_schema.mainEntity' => ['required_with:faq_schema', 'array', 'min:1'],
            'faq_schema.mainEntity.*.@type' => ['required_with:faq_schema', 'string', Rule::in(['Question'])],
            'faq_schema.mainEntity.*.name' => ['required_with:faq_schema', 'string', 'max:500'],
            'faq_schema.mainEntity.*.acceptedAnswer' => ['required_with:faq_schema', 'array'],
            'faq_schema.mainEntity.*.acceptedAnswer.@type' => ['required_with:faq_schema', 'string', Rule::in(['Answer'])],
            'faq_schema.mainEntity.*.acceptedAnswer.text' => ['required_with:faq_schema', 'string', 'max:5000'],
            'group_id' => ['nullable', 'string', 'max:191'],
            'post_id' => ['nullable', 'string', 'max:191'],
            'article_id' => ['nullable', 'string', 'max:191'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasBase64 = filled($this->input('featured_image_base64'));
            $hasMime = filled($this->input('featured_image_mime'));

            if ($hasBase64 xor $hasMime) {
                $validator->errors()->add(
                    'featured_image_base64',
                    'featured_image_base64 y featured_image_mime deben enviarse juntos.'
                );
            }
        });
    }
}
