<?php

namespace App\Rules;

use Illuminate\Validation\Rule;

class FaqSchemaRules
{
    /** @return array<string, mixed> */
    public static function rules(string $prefix = 'faq_schema'): array
    {
        return [
            $prefix => ['nullable', 'array'],
            "$prefix.@type" => ['required_with:' . $prefix, 'string', Rule::in(['FAQPage'])],
            "$prefix.mainEntity" => ['required_with:' . $prefix, 'array', 'min:1'],
            "$prefix.mainEntity.*.@type" => ['required_with:' . $prefix, 'string', Rule::in(['Question'])],
            "$prefix.mainEntity.*.name" => ['required_with:' . $prefix, 'string', 'max:500'],
            "$prefix.mainEntity.*.acceptedAnswer" => ['required_with:' . $prefix, 'array'],
            "$prefix.mainEntity.*.acceptedAnswer.@type" => ['required_with:' . $prefix, 'string', Rule::in(['Answer'])],
            "$prefix.mainEntity.*.acceptedAnswer.text" => ['required_with:' . $prefix, 'string', 'max:5000'],
        ];
    }
}
