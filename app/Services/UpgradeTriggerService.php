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
        $copy = $this->resolvedCopy();
        $locked = $this->lockedFeatures($user);

        return [
            'show_video_trigger' => $locked['videos'] ?? false,
            'show_language_trigger' => ($locked['translation'] ?? false)
                && (($company ? $this->isTourismArea($company) : false) || $this->isForeignAdminSession($request)),
            'tourism_area' => $company ? $this->isTourismArea($company) : false,
            'foreign_session' => $this->isForeignAdminSession($request),
            'pro_price' => $this->plans->proPriceLabel(),
            'plus_price' => $this->plans->plusPriceLabel(),
            'billing_url' => route('admin.settings') . '#plan',
            'copy' => $copy,
            'locked_features' => $locked,
        ];
    }

    /**
     * @return array<string, bool> feature => locked (needs upgrade)
     */
    public function lockedFeatures(User $user): array
    {
        return [
            'templates' => ! $this->plans->hasAllTemplates($user),
            'menu_scan' => ! $this->plans->canUseMenuScan($user),
            'product_photos' => ! $this->plans->canUseProductPhotos($user),
            'videos' => ! $this->plans->canUseVideos($user),
            'video' => ! $this->plans->canUseVideos($user),
            'translation' => ! $this->plans->canUseTranslation($user),
            'pdf_menu' => ! $this->plans->canUsePdfMenu($user),
            'tvpik' => ! $this->plans->canUseTvpik($user),
        ];
    }

    public function isFeatureLocked(User $user, string $feature): bool
    {
        $locked = $this->lockedFeatures($user);

        return $locked[$feature] ?? false;
    }

    public function planBadgeForFeature(string $feature): string
    {
        return $this->plans->requiredPlanLabel($feature) ?? 'Pro';
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
    public function resolvedCopy(): array
    {
        $raw = config('upgrade_triggers.copy', []);
        $resolved = [];

        foreach ($raw as $key => $block) {
            if (! is_array($block)) {
                if (is_string($block)) {
                    $resolved[$key] = [
                        'body' => str_replace(':price', $this->plans->proPriceLabel(), $block),
                    ];
                }

                continue;
            }

            $tierKey = $block['price_tier'] ?? 'pro';
            $price = $tierKey === 'plus'
                ? $this->plans->plusPriceLabel()
                : $this->plans->proPriceLabel();

            $resolved[$key] = [];
            foreach ($block as $field => $text) {
                if ($field === 'price_tier') {
                    continue;
                }
                $resolved[$key][$field] = is_string($text)
                    ? str_replace(':price', $price, $text)
                    : $text;
            }
            $resolved[$key]['price_label'] = $price;
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
