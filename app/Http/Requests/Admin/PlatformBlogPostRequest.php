<?php

namespace App\Http\Requests\Admin;

use App\BlogPost;
use App\Rules\FaqSchemaRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PlatformBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locales = array_keys(config('blog.locales', []));
        $allowedMimes = config('blog.featured_image.allowed_mimes', []);

        $rules = [
            'blog_category_id' => ['nullable', 'integer', Rule::exists('blog_categories', 'id')],
            'status' => ['required', 'string', Rule::in([
                BlogPost::STATUS_DRAFT,
                BlogPost::STATUS_PUBLISHED,
                BlogPost::STATUS_SCHEDULED,
            ])],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'file', 'max:5120', 'mimetypes:image/jpeg,image/png,image/webp,image/gif'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'remove_featured_image' => ['nullable', 'boolean'],
        ];

        foreach ($locales as $locale) {
            $rules["translations.$locale.slug"] = ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.excerpt"] = ['nullable', 'string', 'max:1000'];
            $rules["translations.$locale.body"] = ['nullable', 'string'];
            $rules["translations.$locale.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.$locale.focus_keyword"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.faq_schema"] = ['nullable', 'string'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $locales = array_keys(config('blog.locales', []));
            $hasContent = false;

            foreach ($locales as $locale) {
                $title = trim((string) $this->input("translations.$locale.title", ''));
                $body = trim((string) $this->input("translations.$locale.body", ''));
                if ($title !== '' || $body !== '') {
                    $hasContent = true;
                }

                $faqRaw = $this->input("translations.$locale.faq_schema");
                if ($faqRaw === null || $faqRaw === '') {
                    continue;
                }

                $decoded = json_decode(trim((string) $faqRaw), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $validator->errors()->add("translations.$locale.faq_schema", 'JSON de FAQ no válido.');

                    continue;
                }

                $faqValidator = validator(
                    ['faq_schema' => $decoded],
                    FaqSchemaRules::rules()
                );

                if ($faqValidator->fails()) {
                    foreach ($faqValidator->errors()->all() as $message) {
                        $validator->errors()->add("translations.$locale.faq_schema", $message);
                    }
                }
            }

            if (! $hasContent) {
                $validator->errors()->add('translations', 'Añade al menos un idioma con título o contenido.');
            }
        });
    }
}
