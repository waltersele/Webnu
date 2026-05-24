<?php

namespace App\Services\PreAlta;

use App\MenuPreRegistration;
use App\Services\MenuScan\MenuScanResult;
use Illuminate\Support\Facades\DB;

class PreAltaIngestService
{
    /** @var PreAltaIdentityService */
    protected $identity;

    /** @var PreAltaMediaDownloader */
    protected $mediaDownloader;

    public function __construct(PreAltaIdentityService $identity, PreAltaMediaDownloader $mediaDownloader)
    {
        $this->identity = $identity;
        $this->mediaDownloader = $mediaDownloader;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function ingest(array $payload): array
    {
        $rawSections = $payload['sections'] ?? [];
        $sections = MenuScanResult::normalizeSections($rawSections);
        if (count($sections) === 0) {
            throw new \InvalidArgumentException('No hay secciones válidas en el menú.');
        }

        $token = $this->identity->generateClaimToken();
        $slug = $this->identity->generatePublicSlug();
        $retentionDays = (int) config('pre_alta.retention_days', 20);

        return DB::transaction(function () use ($payload, $rawSections, $sections, $token, $slug, $retentionDays) {
            $registration = MenuPreRegistration::create([
                'restaurant_name' => trim((string) $payload['restaurant_name']),
                'menu_json' => ['sections' => []],
                'public_slug' => $slug,
                'claim_token_hash' => $token['hash'],
                'status' => MenuPreRegistration::STATUS_PENDING,
                'media_manifest' => [],
                'source_meta' => $payload['source_meta'] ?? null,
                'expires_at' => now()->addDays($retentionDays),
            ]);

            $processed = $this->mediaDownloader->process(
                (int) $registration->id,
                is_array($rawSections) ? $rawSections : [],
                $payload['logo_url'] ?? null
            );

            $manifest = $processed['manifest'];
            foreach ($sections as $si => $section) {
                foreach ($section['products'] as $pi => $product) {
                    $stagingKey = "s{$si}_p{$pi}";
                    if (isset($manifest[$stagingKey])) {
                        $sections[$si]['products'][$pi]['_staging_image'] = $manifest[$stagingKey];
                    }
                }
            }

            $registration->menu_json = ['sections' => $sections];
            $registration->media_manifest = $manifest;
            $registration->save();

            return [
                'id' => $registration->id,
                'public_url' => url('/pre-alta/' . $registration->public_slug),
                'claim_url' => url('/activar/' . $token['plain']),
                'expires_at' => $registration->expires_at->toIso8601String(),
            ];
        });
    }
}
