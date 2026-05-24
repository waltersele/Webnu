<?php

namespace App\Services\PreAlta;

use App\Company;
use App\MenuPreRegistration;
use App\Product;
use App\Section;
use App\Services\CompanySlugService;
use App\Services\MenuImportService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PreAltaClaimService
{
    /** @var MenuImportService */
    protected $menuImport;

    /** @var CompanySlugService */
    protected $slugService;

    /** @var PreAltaMediaDownloader */
    protected $mediaDownloader;

    public function __construct(
        MenuImportService $menuImport,
        CompanySlugService $slugService,
        PreAltaMediaDownloader $mediaDownloader
    ) {
        $this->menuImport = $menuImport;
        $this->slugService = $slugService;
        $this->mediaDownloader = $mediaDownloader;
    }

    /**
     * @param array{name: string, email: string, password: string} $credentials
     * @return array{user: User, company: Company}
     */
    public function claim(string $plainToken, array $credentials): array
    {
        $hash = MenuPreRegistration::hashClaimToken($plainToken);

        $registration = MenuPreRegistration::where('claim_token_hash', $hash)
            ->where('status', MenuPreRegistration::STATUS_PENDING)
            ->first();

        if (! $registration) {
            throw new \RuntimeException('Enlace de activación no válido o ya utilizado.');
        }

        if ($registration->isExpired()) {
            throw new \RuntimeException('Este enlace de activación ha caducado.');
        }

        $user = null;
        $company = null;

        DB::transaction(function () use ($registration, $credentials, &$user, &$company) {
            $locked = MenuPreRegistration::where('id', $registration->id)
                ->where('status', MenuPreRegistration::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->isExpired()) {
                throw new \RuntimeException('Este enlace de activación ya no está disponible.');
            }

            if (User::where('email', $credentials['email'])->exists()) {
                throw new \RuntimeException('Ya existe una cuenta con ese email.');
            }

            $user = User::create([
                'name' => $credentials['name'],
                'email' => $credentials['email'],
                'password' => Hash::make($credentials['password']),
                'plan' => 'free',
                'onboarding_step' => 6,
                'onboarding_completed_at' => now(),
                'trial_ends_at' => now()->addDays((int) config('plans.trial_days', 30)),
                'trial_plan_key' => config('plans.trial_tier', 'pro'),
            ]);

            $slug = $this->slugService->generateFromName($locked->restaurant_name);

            $company = Company::create([
                'name' => $locked->restaurant_name,
                'slug' => $slug,
                'template' => 'lumiere',
                'menu_type' => 1,
                'enabled' => true,
                'reservation' => false,
                'user_id' => $user->id,
            ]);

            $menuJson = $locked->menu_json ?? ['sections' => []];
            $this->menuImport->import($company, $menuJson, MenuImportService::MODE_REPLACE);

            $locked->status = MenuPreRegistration::STATUS_CLAIMED;
            $locked->claimed_user_id = $user->id;
            $locked->claimed_at = now();
            $locked->menu_json = null;
            $locked->save();

            $registration = $locked;
        });

        try {
            $this->migrateMediaToProduction($registration, $company);
            $this->purgeStagingDirectory($registration);
        } catch (\Throwable $e) {
            $this->compensateFailedClaim($registration, $user);
            Log::error('Pre-Alta: fallo migrando medios', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('No se pudieron migrar las imágenes. Inténtalo de nuevo o contacta con soporte.');
        }

        Cookie::queue(Cookie::forever('selected_company', $company->id));
        Auth::login($user);

        return [
            'user' => $user,
            'company' => $company,
        ];
    }

    protected function migrateMediaToProduction(MenuPreRegistration $registration, Company $company): void
    {
        $manifest = $registration->media_manifest ?? [];
        if ($manifest === []) {
            return;
        }

        $sections = Section::where('company_id', $company->id)
            ->orderBy('order')
            ->with(['products' => function ($q) {
                $q->orderBy('order');
            }])
            ->get();

        foreach ($sections as $si => $section) {
            foreach ($section->products as $pi => $product) {
                $key = "s{$si}_p{$pi}";
                if (! isset($manifest[$key])) {
                    continue;
                }
                $destRelative = $this->moveStagingFileToPublic($manifest[$key], 'productos');
                if ($destRelative) {
                    $product->image = $destRelative;
                    $product->save();
                }
            }
        }

        if (! empty($manifest['logo'])) {
            $logoRelative = $this->moveStagingFileToPublic($manifest['logo'], 'negocios');
            if ($logoRelative) {
                $company->logo = $logoRelative;
                $company->save();
            }
        }
    }

    protected function moveStagingFileToPublic(string $stagingRelative, string $targetFolder): ?string
    {
        $source = $this->mediaDownloader->absolutePath($stagingRelative);
        if (! is_file($source)) {
            return null;
        }

        $basename = basename($stagingRelative);
        $destDir = public_path('img/' . trim($targetFolder, '/'));
        if (! is_dir($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $destFile = $destDir . DIRECTORY_SEPARATOR . $basename;
        if (! @rename($source, $destFile)) {
            if (! @copy($source, $destFile)) {
                throw new \RuntimeException("No se pudo mover el archivo: {$stagingRelative}");
            }
            @unlink($source);
        }

        return trim($targetFolder, '/') . '/' . $basename;
    }

    protected function purgeStagingDirectory(MenuPreRegistration $registration): void
    {
        $dir = Storage::disk('pre_alta')->path((string) $registration->id);
        if (is_dir($dir)) {
            File::deleteDirectory($dir);
        }
    }

    protected function compensateFailedClaim(MenuPreRegistration $registration, User $user): void
    {
        DB::transaction(function () use ($registration, $user) {
            $userId = $user->id;
            Company::where('user_id', $userId)->each(function (Company $company) {
                $sectionIds = Section::where('company_id', $company->id)->pluck('id');
                Product::whereIn('section_id', $sectionIds)->delete();
                Section::where('company_id', $company->id)->delete();
                $company->delete();
            });
            User::where('id', $userId)->delete();

            MenuPreRegistration::where('id', $registration->id)->update([
                'status' => MenuPreRegistration::STATUS_PENDING,
                'claimed_user_id' => null,
                'claimed_at' => null,
            ]);
        });
    }
}
