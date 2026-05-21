<?php

namespace App\Services;

use App\Company;
use App\User;
use Illuminate\Http\Request;

class UpgradeTriggerService
{
    protected UserPlanService $plans;

    public function __construct(UserPlanService $plans)
    {
        $this->plans = $plans;
    }

    /**
     * @return array<string, mixed>
     */
    public function contextFor(User $user, ?Company $company, Request $request): array
    {
        $canVideos = $this->plans->canUseVideos($user);
        $canTranslation = $this->plans->canUseTranslation($user);
        $tourismArea = $company ? $this->isTourismArea($company) : false;
        $foreignSession = $this->isForeignAdminSession($request);
        $plusPrice = config('plans.tiers.plus.price_label', '9,90 €/mes');
        $copy = $this->resolvedCopy($plusPrice);

        return [
            'show_video_trigger' => ! $canVideos,
            'show_language_trigger' => ! $canTranslation && ($tourismArea || $foreignSession),
            'tourism_area' => $tourismArea,
            'foreign_session' => $foreignSession,
            'plus_price' => $plusPrice,
            'billing_url' => route('admin.settings'),
            'copy' => $copy,
        ];
    }

    public function isTourismArea(Company $company): bool
    {
        if ($company->suggest_translation_upgrade) {
            return true;
        }

        $province = $this->normalizePlace($company->province ?? '');
        $city = $this->normalizePlace($company->city ?? '');

        foreach (config('upgrade_triggers.tourism_provinces', []) as $needle) {
            $needle = $this->normalizePlace($needle);
            if ($needle !== '' && $province !== '' && (str_contains($province, $needle) || str_contains($needle, $province))) {
                return true;
            }
        }

        foreach (config('upgrade_triggers.tourism_cities', []) as $needle) {
            $needle = $this->normalizePlace($needle);
            if ($needle !== '' && $city !== '' && str_contains($city, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function isForeignAdminSession(Request $request): bool
    {
        $home = strtoupper((string) config('upgrade_triggers.home_country', 'ES'));
        $country = $request->header('CF-IPCountry')
            ?? $request->header('X-App-Country')
            ?? $request->header('X-Country-Code');

        if (! is_string($country) || trim($country) === '') {
            return false;
        }

        return strtoupper(trim($country)) !== $home;
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function resolvedCopy(string $plusPrice): array
    {
        $raw = config('upgrade_triggers.copy', []);
        $resolved = [];

        foreach ($raw as $key => $block) {
            if (! is_array($block)) {
                continue;
            }
            $resolved[$key] = [];
            foreach ($block as $field => $text) {
                $resolved[$key][$field] = is_string($text)
                    ? str_replace(':price', $plusPrice, $text)
                    : $text;
            }
        }

        return $resolved;
    }

    protected function normalizePlace(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $value
        );

        return $value;
    }
}
