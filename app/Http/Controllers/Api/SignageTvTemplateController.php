<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserPlanService;
use Illuminate\Http\Request;

class SignageTvTemplateController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $plans = app(UserPlanService::class);
        $access = $plans->tvpikTemplateAccessForUser($user);
        $lockedKeys = array_flip($access['locked_keys']);

        $templates = [];
        foreach (config('tvpik_templates.templates', []) as $key => $template) {
            $templateKey = (string) ($template['key'] ?? $key);
            $thumbnail = $template['thumbnail'] ?? null;
            $isPremium = ! empty($template['premium']);

            $entry = [
                'key' => $templateKey,
                'label' => (string) ($template['label'] ?? $templateKey),
                'description' => (string) ($template['description'] ?? ''),
                'duration_hint' => (string) ($template['duration_hint'] ?? ''),
                'layout' => (string) ($template['layout'] ?? $templateKey),
                'thumbnail_url' => $thumbnail ? url($thumbnail) : null,
                'locked' => isset($lockedKeys[$templateKey]),
            ];

            if (! empty($template['category'])) {
                $entry['category'] = (string) $template['category'];
            }

            if ($isPremium) {
                $entry['premium'] = true;
            }

            if ($entry['locked'] && $isPremium && $plans->canUseTvpik($user)) {
                $entry['locked_reason'] = 'requires_plus';
            }

            $templates[] = $entry;
        }

        return response()->json([
            'api_version' => config('digital_signage.api_version'),
            'default_key' => (string) config('tvpik_templates.default', 'menu'),
            'categories' => $this->buildCategories($templates),
            'templates' => $templates,
            'features' => [
                'tvpik_premium_templates' => $access['can_use_premium'],
            ],
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $templates
     * @return list<array{key: string, label: string}>
     */
    protected function buildCategories(array $templates): array
    {
        $categoryConfig = config('tvpik_templates.categories', []);
        $usedKeys = [];

        foreach ($templates as $template) {
            $category = $template['category'] ?? null;
            if (is_string($category) && $category !== '') {
                $usedKeys[$category] = true;
            }
        }

        $categories = [];
        foreach (array_keys($usedKeys) as $key) {
            $config = is_array($categoryConfig[$key] ?? null) ? $categoryConfig[$key] : [];
            $label = (string) ($config['label'] ?? $this->humanizeCategoryKey($key));
            $categories[] = [
                'key' => $key,
                'label' => $label,
            ];
        }

        usort($categories, fn ($a, $b) => strcmp($a['key'], $b['key']));

        return $categories;
    }

    protected function humanizeCategoryKey(string $key): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $key));
    }
}
