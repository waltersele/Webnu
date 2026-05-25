<?php

namespace App\Services\Demo;

use App\Allergen;
use App\Company;
use App\Product;
use App\Section;
use App\Services\AllergenCatalogService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Servicio idempotente para sembrar las cartas demo (mismas que /carta/demo,
 * /carta/demo-cocktails, etc.) tanto en local como en producción.
 *
 * No descarga imágenes ni vídeos: asume que los assets ya existen en
 * `public/img/` (los desplegamos vía git). En producción esto evita
 * dependencias de red en tiempo de seed.
 */
class DemoMenuSeeder
{
    /** @var callable|null  fn(string $message): void */
    protected $logger;

    public function __construct(?callable $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Crea/actualiza el usuario propietario y todas las cartas demo definidas
     * por {@see DemoCompanyDataProvider}. Devuelve el número de companies
     * resultantes.
     */
    public function seed(string $ownerEmail = 'demo@webnu.es', string $ownerPassword = 'demo123'): int
    {
        $owner = $this->ensureOwner($ownerEmail, $ownerPassword);

        app(AllergenCatalogService::class)->sync();
        $allergens = Allergen::orderBy('name')->get();

        $provider = new DemoCompanyDataProvider();
        $count = 0;

        foreach ($provider->companies() as $config) {
            $this->log("→ {$config['name']} (/carta/{$config['slug']})");
            $this->seedCompany($config, $owner, $allergens);
            $count++;
        }

        return $count;
    }

    protected function ensureOwner(string $email, string $password): User
    {
        $owner = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Webnu Demo',
                'password' => Hash::make($password),
                'plan' => 'plus',
            ]
        );

        if ($owner->wasRecentlyCreated && empty($owner->email_verified_at)) {
            $owner->email_verified_at = now();
        }
        $owner->plan = 'plus';
        $owner->save();

        return $owner;
    }

    /**
     * @param array<string, mixed> $config
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Allergen> $allergens
     */
    protected function seedCompany(array $config, User $owner, $allergens): Company
    {
        return DB::transaction(function () use ($config, $owner, $allergens) {
            $company = Company::firstOrCreate(
                ['slug' => $config['slug']],
                [
                    'name' => $config['name'],
                    'chef_name' => $config['chef_name'] ?? null,
                    'address' => $config['address'] ?? 'Muelle Poniente, 12',
                    'postal_code' => $config['postal_code'] ?? '03001',
                    'city' => $config['city'] ?? 'Alicante',
                    'province' => $config['province'] ?? 'Alicante',
                    'country' => $config['country'] ?? 'España',
                    'phone' => $config['phone'] ?? '965214087',
                    'mobile_phone' => $config['mobile_phone'] ?? '665214087',
                    'email' => $config['email'] ?? 'demo@webnu.es',
                    'web' => $config['web'] ?? 'https://webnu.es',
                    'whatsapp' => $config['whatsapp'] ?? '34665214087',
                    'menu_type' => 1,
                    'enabled' => true,
                    'user_id' => $owner->id,
                    'reservation' => 1,
                    'template' => $config['template'],
                    'comments' => $config['comments'] ?? null,
                    'schedule' => $config['schedule'] ?? "Mar–Dom 13:00–16:00 · 20:00–23:30\nLunes cerrado",
                    'instagram' => $config['instagram'] ?? null,
                ]
            );

            $company->user_id = $owner->id;
            $company->name = $config['name'];
            $company->chef_name = $config['chef_name'] ?? $company->chef_name;
            $company->template = $config['template'];
            $company->comments = $config['comments'] ?? $company->comments;
            $company->background_header = $config['background_header'] ?? $company->background_header;
            $company->logo = $config['logo'] ?? $company->logo;
            if (! empty($config['theme_settings'])) {
                $company->theme_settings = $config['theme_settings'];
            }
            $company->enabled = true;
            if (! empty($config['enabled_locales'])) {
                $company->enabled_locales = $config['enabled_locales'];
                $company->default_locale = $config['default_locale'] ?? 'es';
            }
            $company->save();

            $existingSectionIds = Section::where('company_id', $company->id)->pluck('id');
            if ($existingSectionIds->isNotEmpty()) {
                Product::whereIn('section_id', $existingSectionIds)->each(function (Product $product) {
                    $product->allergens()->detach();
                    $product->delete();
                });
                Section::where('company_id', $company->id)->delete();
            }

            $sections = [];
            foreach ($config['sections'] as $name => $order) {
                $sections[$name] = Section::create([
                    'company_id' => $company->id,
                    'name' => $name,
                    'order' => $order,
                    'enabled' => 1,
                ]);
            }

            $sectionOrders = [];
            foreach ($config['dishes'] as $dish) {
                if (! isset($sections[$dish['section']])) {
                    continue;
                }
                $section = $sections[$dish['section']];
                $order = $sectionOrders[$dish['section']] ?? 0;

                $product = Product::create([
                    'section_id' => $section->id,
                    'name' => $dish['name'],
                    'description' => $dish['description'],
                    'price_unit' => $dish['price'],
                    'price_portion' => null,
                    'individual_sale' => false,
                    'weight_sale' => false,
                    'image' => isset($dish['image']) ? 'productos/' . $dish['image'] : null,
                    'video' => $this->resolveVideoPath($dish['video'] ?? null),
                    'order' => $order,
                    'enabled' => true,
                    'highlight' => $dish['highlight'] ?? null,
                ]);

                $ids = $allergens->filter(function ($a) use ($dish) {
                    return in_array($a->name, $dish['allergens'] ?? [], true);
                })->pluck('id')->all();

                $product->allergens()->sync($ids);
                $sectionOrders[$dish['section']] = $order + 1;
            }

            return $company;
        });
    }

    protected function resolveVideoPath(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        $meta = config('demo_media.videos.' . $key);

        return $meta ? 'demo/' . $meta['file'] : null;
    }

    protected function log(string $message): void
    {
        if ($this->logger) {
            call_user_func($this->logger, $message);
        }
    }
}
