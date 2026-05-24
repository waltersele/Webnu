<?php

namespace App\Http\Requests\PreAlta;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PreAltaIngestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $maxSections = (int) config('pre_alta.max_sections', 50);
        $maxPerSection = (int) config('pre_alta.max_products_per_section', 100);

        return [
            'restaurant_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN\s\.\-\&\'\´\`\,]+$/u'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'sections' => ['required', 'array', 'min:1', 'max:' . $maxSections],
            'sections.*.name' => ['required', 'string', 'max:255'],
            'sections.*.products' => ['required', 'array', 'min:1', 'max:' . $maxPerSection],
            'sections.*.products.*.name' => ['required', 'string', 'max:255'],
            'sections.*.products.*.description' => ['nullable', 'string', 'max:1000'],
            'sections.*.products.*.price_unit' => ['nullable', 'string', 'max:32'],
            'sections.*.products.*.price_portion' => ['nullable', 'string', 'max:32'],
            'sections.*.products.*.price' => ['nullable', 'string', 'max:32'],
            'sections.*.products.*.image_url' => ['nullable', 'url', 'max:2048'],
            'sections.*.products.*.allergens' => ['nullable', 'array', 'max:20'],
            'sections.*.products.*.allergens.*' => ['string', 'max:64'],
            'source_meta' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $total = 0;
            foreach ($this->input('sections', []) as $section) {
                $total += count($section['products'] ?? []);
            }
            $maxTotal = (int) config('pre_alta.max_total_products', 500);
            if ($total > $maxTotal) {
                $validator->errors()->add('sections', "Máximo {$maxTotal} platos en total.");
            }

            foreach (['logo_url'] as $urlField) {
                $url = $this->input($urlField);
                if ($url && ! $this->isAllowedHttpUrl($url)) {
                    $validator->errors()->add($urlField, 'Solo se permiten URLs http/https.');
                }
            }

            foreach ($this->input('sections', []) as $si => $section) {
                foreach ($section['products'] ?? [] as $pi => $product) {
                    $url = $product['image_url'] ?? null;
                    if ($url && ! $this->isAllowedHttpUrl($url)) {
                        $validator->errors()->add("sections.{$si}.products.{$pi}.image_url", 'Solo se permiten URLs http/https.');
                    }
                }
            }
        });
    }

    protected function isAllowedHttpUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return false;
        }

        $allowedHosts = config('pre_alta.allowed_image_hosts', []);
        if ($allowedHosts === []) {
            return true;
        }

        $host = strtolower($parts['host'] ?? '');

        return in_array($host, array_map('strtolower', $allowedHosts), true);
    }
}
