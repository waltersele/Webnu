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
}
