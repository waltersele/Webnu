<?php

namespace App\Services\Lifecycle;

use App\Company;
use App\Mail\Lifecycle\CartaInactiveMail;
use App\Mail\Lifecycle\CartaPublishedMail;
use App\Mail\Lifecycle\FeatureNudgeMail;
use App\Mail\Lifecycle\LifecycleMail;
use App\Mail\Lifecycle\OnboardingReminderMail;
use App\Mail\Lifecycle\QrOnTableMail;
use App\Mail\Lifecycle\SubscriptionStartedMail;
use App\Mail\Lifecycle\TrialEndingSoonMail;
use App\Mail\Lifecycle\TrialExpiredMail;
use App\Mail\Lifecycle\WelcomeMail;
use App\Mail\Lifecycle\WinBackMail;
use App\Product;
use App\Section;
use App\User;
use App\UserLifecycleEmail;
use App\Services\UserPlanService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class LifecycleMailer
{
    public const KEY_WELCOME = 'welcome';
    public const KEY_ONBOARDING_REMINDER = 'onboarding_reminder';
    public const KEY_CARTA_INACTIVE = 'carta_inactive';
    public const KEY_CARTA_PUBLISHED = 'carta_published';
    public const KEY_QR_ON_TABLE = 'qr_on_table';
    public const KEY_TRIAL_7 = 'trial_ending_7';
    public const KEY_TRIAL_1 = 'trial_ending_1';
    public const KEY_TRIAL_EXPIRED = 'trial_expired';
    public const KEY_WINBACK_3 = 'winback_3';
    public const KEY_WINBACK_14 = 'winback_14';
    public const KEY_SUBSCRIPTION_STARTED = 'subscription_started';
    public const KEY_NUDGE_IA = 'nudge_ia';
    public const KEY_NUDGE_LOCALES = 'nudge_locales';
    public const KEY_NUDGE_VIDEOS = 'nudge_videos';
    public const KEY_NUDGE_TVPIK = 'nudge_tvpik';
    public const KEY_NUDGE_QR = 'nudge_qr';

    protected UserPlanService $plans;

    public function __construct(UserPlanService $plans)
    {
        $this->plans = $plans;
    }

    public function tablesReady(): bool
    {
        try {
            return Schema::hasTable('user_lifecycle_emails');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function alreadySent(User $user, string $templateKey): bool
    {
        if (! $this->tablesReady()) {
            return true;
        }

        return UserLifecycleEmail::query()
            ->where('user_id', $user->id)
            ->where('template_key', $templateKey)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function sendOnce(User $user, string $templateKey, LifecycleMail $mailable): bool
    {
        if (! $user->email || $this->alreadySent($user, $templateKey)) {
            return false;
        }

        try {
            Mail::send($mailable);
            UserLifecycleEmail::create([
                'user_id' => $user->id,
                'template_key' => $templateKey,
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('LifecycleMailer send failed', [
                'user_id' => $user->id,
                'template' => $templateKey,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendWelcome(User $user): bool
    {
        return $this->sendOnce($user, self::KEY_WELCOME, new WelcomeMail($user, [
            'cta_url' => route('admin.onboarding'),
            'cta_label' => 'Completar mi carta',
        ]));
    }

    public function sendCartaPublished(User $user): bool
    {
        $company = $user->companies()->orderBy('id')->first();
        $qrUrl = $company
            ? route('admin.qrgenerator', $company)
            : url('/admin');

        return $this->sendOnce($user, self::KEY_CARTA_PUBLISHED, new CartaPublishedMail($user, [
            'cta_url' => $qrUrl,
            'cta_label' => 'Descargar QR',
            'company_name' => $company->name ?? '',
        ]));
    }

    public function sendSubscriptionStarted(User $user, string $plan = ''): bool
    {
        return $this->sendOnce($user, self::KEY_SUBSCRIPTION_STARTED, new SubscriptionStartedMail($user, [
            'cta_url' => url('/admin'),
            'cta_label' => 'Ir al panel',
            'plan' => $plan,
        ]));
    }

    public function sendTrialEnding(User $user, int $daysLeft): bool
    {
        $key = $daysLeft <= 1 ? self::KEY_TRIAL_1 : self::KEY_TRIAL_7;

        return $this->sendOnce($user, $key, new TrialEndingSoonMail($user, [
            'days_left' => $daysLeft,
            'cta_url' => url('/admin/billing'),
            'cta_label' => 'Activar Pro',
        ]));
    }

    public function sendTrialExpired(User $user): bool
    {
        return $this->sendOnce($user, self::KEY_TRIAL_EXPIRED, new TrialExpiredMail($user, [
            'cta_url' => url('/admin/billing'),
            'cta_label' => 'Reactivar Pro',
        ]));
    }

    public function sendWinBack(User $user, int $daysAfter): bool
    {
        $key = $daysAfter >= 14 ? self::KEY_WINBACK_14 : self::KEY_WINBACK_3;

        return $this->sendOnce($user, $key, new WinBackMail($user, [
            'days_after' => $daysAfter,
            'cta_url' => url('/admin/billing'),
            'cta_label' => 'Volver a Pro',
        ]));
    }

    public function sendOnboardingReminder(User $user): bool
    {
        $step = max(1, (int) ($user->onboarding_step ?: 1));

        return $this->sendOnce($user, self::KEY_ONBOARDING_REMINDER, new OnboardingReminderMail($user, [
            'cta_url' => route('admin.onboarding', ['step' => $step]),
            'cta_label' => 'Continuar configuración',
            'step' => $step,
        ]));
    }

    public function sendCartaInactive(User $user): bool
    {
        return $this->sendOnce($user, self::KEY_CARTA_INACTIVE, new CartaInactiveMail($user, [
            'cta_url' => route('admin.onboarding'),
            'cta_label' => 'Publicar mi carta',
        ]));
    }

    public function sendQrOnTable(User $user, Company $company): bool
    {
        return $this->sendOnce($user, self::KEY_QR_ON_TABLE, new QrOnTableMail($user, [
            'cta_url' => route('admin.qrgenerator', $company),
            'cta_label' => 'Descargar QR',
        ]));
    }

    /**
     * Máx. 1 nudge/semana por usuario.
     */
    public function sendFeatureNudgeIfDue(User $user): bool
    {
        if (! $this->tablesReady() || $this->sentNudgeThisWeek($user)) {
            return false;
        }

        $company = $user->companies()->orderBy('id')->first();
        if (! $company || ! $company->enabled) {
            return false;
        }

        $flags = $this->plans->featureFlags($user);
        $nudge = $this->resolveNudge($user, $company, $flags);
        if ($nudge === null) {
            return false;
        }

        return $this->sendOnce($user, $nudge['key'], new FeatureNudgeMail($user, [
            'subject' => $nudge['subject'],
            'body' => $nudge['body'],
            'cta_url' => $nudge['cta_url'],
            'cta_label' => $nudge['cta_label'],
        ]));
    }

    protected function sentNudgeThisWeek(User $user): bool
    {
        $nudgeKeys = [
            self::KEY_NUDGE_IA,
            self::KEY_NUDGE_LOCALES,
            self::KEY_NUDGE_VIDEOS,
            self::KEY_NUDGE_TVPIK,
            self::KEY_NUDGE_QR,
        ];

        return UserLifecycleEmail::query()
            ->where('user_id', $user->id)
            ->whereIn('template_key', $nudgeKeys)
            ->where('sent_at', '>=', now()->subDays(7))
            ->exists();
    }

    /**
     * @param  array<string, bool>  $flags
     * @return array{key: string, subject: string, body: string, cta_url: string, cta_label: string}|null
     */
    protected function resolveNudge(User $user, Company $company, array $flags): ?array
    {
        if (! empty($flags['menu_scan']) && ! $this->alreadySent($user, self::KEY_NUDGE_IA)) {
            $hasProducts = $this->companyHasProducts($company);
            if (! $hasProducts) {
                return [
                    'key' => self::KEY_NUDGE_IA,
                    'subject' => 'Sube foto o PDF y rellena la carta con IA',
                    'body' => 'Fotografía tu carta en papel o sube un PDF: la IA importa platos, precios y alérgenos en minutos.',
                    'cta_url' => route('admin.menu-scan.create'),
                    'cta_label' => 'Importar con IA',
                ];
            }
        }

        if (! empty($flags['translation']) && ! $this->alreadySent($user, self::KEY_NUDGE_LOCALES)) {
            $extra = is_array($company->enabled_locales) ? count($company->enabled_locales) : 0;
            if ($extra === 0) {
                return [
                    'key' => self::KEY_NUDGE_LOCALES,
                    'subject' => 'Menú en el idioma del móvil del cliente',
                    'body' => 'Activa idiomas extra y tus clientes verán la carta automáticamente en su idioma.',
                    'cta_url' => route('admin.companies.edit', $company),
                    'cta_label' => 'Configurar idiomas',
                ];
            }
        }

        if (! empty($flags['videos']) && ! $this->alreadySent($user, self::KEY_NUDGE_VIDEOS)) {
            if (! $this->companyHasVideos($company)) {
                return [
                    'key' => self::KEY_NUDGE_VIDEOS,
                    'subject' => 'Platos con vídeo venden más',
                    'body' => 'Añade un vídeo o reel a tus platos estrella: el cliente ve el plato antes de pedirlo.',
                    'cta_url' => route('admin.sections.index'),
                    'cta_label' => 'Añadir vídeos',
                ];
            }
        }

        if (! empty($flags['tvpik']) && ! $this->alreadySent($user, self::KEY_NUDGE_TVPIK)) {
            $connected = false;
            if (Schema::hasTable('tvpik_screens')) {
                $connected = \DB::table('tvpik_screens')->where('user_id', $user->id)->exists();
            } elseif (Schema::hasTable('tv_screens')) {
                $connected = \DB::table('tv_screens')->where('user_id', $user->id)->exists();
            }
            if (! $connected) {
                return [
                    'key' => self::KEY_NUDGE_TVPIK,
                    'subject' => 'Tu carta en la TV del local',
                    'body' => 'Con TVPik muestras la carta digital en la pantalla del local y se actualiza sola.',
                    'cta_url' => route('admin.tvpik.index'),
                    'cta_label' => 'Conectar TV',
                ];
            }
        }

        if (! $this->alreadySent($user, self::KEY_NUDGE_QR)
            && (int) ($company->menu_views ?? 0) === 0
        ) {
            return [
                'key' => self::KEY_NUDGE_QR,
                'subject' => 'Imprime el QR y colócalo en mesa',
                'body' => 'Tu carta está publicada pero aún no tiene escaneos. Descarga el QR e imprímelo.',
                'cta_url' => route('admin.qrgenerator', $company),
                'cta_label' => 'Descargar QR',
            ];
        }

        return null;
    }

    protected function companyHasProducts(Company $company): bool
    {
        $sectionIds = Section::where('company_id', $company->id)->pluck('id');
        if ($sectionIds->isEmpty()) {
            return false;
        }

        return Product::whereIn('section_id', $sectionIds)->exists();
    }

    protected function companyHasVideos(Company $company): bool
    {
        $sectionIds = Section::where('company_id', $company->id)->pluck('id');
        if ($sectionIds->isEmpty()) {
            return false;
        }

        $q = Product::whereIn('section_id', $sectionIds);
        if (Schema::hasColumn('products', 'video')) {
            return $q->whereNotNull('video')->where('video', '!=', '')->exists();
        }
        if (Schema::hasColumn('products', 'video_url')) {
            return $q->whereNotNull('video_url')->where('video_url', '!=', '')->exists();
        }

        return false;
    }
}
