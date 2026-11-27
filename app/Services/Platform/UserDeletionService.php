<?php

namespace App\Services\Platform;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina una cuenta de usuario.
 *
 * Regla permanente del proyecto (CLAUDE.md): jamás se borra un usuario sin una copia
 * verificada previa. Por eso, ANTES de tocar nada:
 *   1. se guarda un snapshot JSON del usuario y de todos sus datos relacionados;
 *   2. se vuelve a leer y se comprueba su hash;
 *   3. si algo de lo anterior falla, NO se borra nada (se lanza RuntimeException).
 * Además se deja constancia en el log "account_deletions" (quién, cuándo y qué).
 */
class UserDeletionService
{
    public function delete(User $user, ?User $actor = null): void
    {
        if ($user->isSuperAdmin()) {
            throw new \InvalidArgumentException('No se puede eliminar una cuenta superadmin desde el panel.');
        }

        $userId = (int) $user->id;
        $isSelf = $actor && (int) $actor->id === $userId;

        // 1) Copia de seguridad verificada ANTES de cualquier cambio.
        $snapshot = $this->saveSnapshot($user, $actor, $isSelf);

        $context = [
            'user_id' => $userId,
            'email' => $user->email,
            'name' => $user->name,
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email,
            'self_service' => (bool) $isSelf,
            'ip' => app()->runningInConsole() ? null : request()->ip(),
            'snapshot' => $snapshot['path'],
            'snapshot_sha256' => $snapshot['sha256'],
            'rows' => $snapshot['counts'],
        ];
        $this->audit('info', 'user_deletion_started', $context);

        // 2) Cancelar la suscripción en Stripe (si falla, se continúa pero queda avisado en el log).
        $stripeCancelError = null;
        $subscription = $user->primarySubscription();
        if ($subscription && $subscription->stripe_status !== 'canceled') {
            try {
                $subscription->cancelNow();
            } catch (\Throwable $e) {
                report($e);
                $stripeCancelError = $e->getMessage();
                $this->audit('warning', 'user_deletion_stripe_cancel_failed', $context + [
                    'stripe_subscription_id' => $subscription->stripe_id,
                    'stripe_customer_id' => $user->stripe_id,
                    'error' => $stripeCancelError,
                ]);
            }
        }

        // 3) Borrado en una transacción: si algo falla, no queda a medias.
        try {
            DB::transaction(function () use ($user) {
                $user->subscriptions()->delete();
                $user->roles()->detach();
                if (method_exists($user, 'permissions')) {
                    $user->permissions()->detach();
                }

                if (! $user->delete()) {
                    throw new \RuntimeException('No se pudo eliminar la cuenta.');
                }
            });
        } catch (\Throwable $e) {
            $this->audit('error', 'user_deletion_failed', $context + ['error' => $e->getMessage()]);
            throw $e;
        }

        $this->audit('info', 'user_deleted', $context + ['stripe_cancel_error' => $stripeCancelError]);

        if ($isSelf && Auth::id() === $userId) {
            Auth::logout();
        }
    }

    /**
     * Guarda y verifica el snapshot. Lanza RuntimeException si no puede garantizarlo.
     *
     * @return array{path: string, sha256: string, counts: array<string,int>}
     */
    private function saveSnapshot(User $user, ?User $actor, bool $isSelf): array
    {
        try {
            $dir = rtrim((string) config('platform.deleted_users_path', storage_path('app/deleted-users')), '/\\');
            if (! File::isDirectory($dir)) {
                @File::makeDirectory($dir, 0750, true);
            }
            if (! File::isDirectory($dir) || ! is_writable($dir)) {
                throw new \RuntimeException("El directorio de copias no es escribible: {$dir}");
            }

            $tables = $this->collectData($user);
            $counts = array_map('count', $tables);

            $payload = [
                'meta' => [
                    'deleted_at' => now()->toIso8601String(),
                    'user_id' => (int) $user->id,
                    'email' => $user->email,
                    'actor_id' => $actor?->id,
                    'actor_email' => $actor?->email,
                    'self_service' => $isSelf,
                    'format' => 1,
                ],
                'rows' => $counts,
                'tables' => $tables,
            ];
            $json = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );

            $path = $dir . '/user-' . (int) $user->id . '-' . now()->format('Ymd-His') . '.json';
            if (file_put_contents($path, $json, LOCK_EX) === false) {
                throw new \RuntimeException("No se pudo escribir {$path}");
            }
            @chmod($path, 0600);

            // Verificación: se vuelve a leer del disco y debe coincidir exactamente.
            $sha = hash('sha256', $json);
            if (! is_file($path) || hash_file('sha256', $path) !== $sha) {
                throw new \RuntimeException("La copia {$path} no coincide con lo escrito");
            }
            $check = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            if (count($check['tables']['users'] ?? []) !== 1) {
                throw new \RuntimeException("La copia {$path} no contiene al usuario");
            }

            return ['path' => $path, 'sha256' => $sha, 'counts' => $counts];
        } catch (\Throwable $e) {
            report($e);
            $this->audit('error', 'user_deletion_aborted_no_snapshot', [
                'user_id' => (int) $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException(
                'No se pudo guardar la copia previa: la cuenta NO se ha eliminado.',
                0,
                $e
            );
        }
    }

    /**
     * Filas crudas (tal y como están en la BD, listas para restaurar) del usuario y de todo
     * lo que cuelga de él por claves foráneas.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function collectData(User $user): array
    {
        $uid = [(int) $user->id];
        $t = [];

        $t['users'] = $this->rows('users', ['id' => $uid]);
        $t['subscriptions'] = $this->rows('subscriptions', ['user_id' => $uid]);
        $t['subscription_items'] = $this->rows('subscription_items', ['subscription_id' => $this->ids($t['subscriptions'])]);

        $morph = $user->getMorphClass();
        foreach (['model_has_roles', 'model_has_permissions'] as $key) {
            $table = (string) config("permission.table_names.{$key}", $key);
            $t[$key] = Schema::hasTable($table)
                ? DB::table($table)->where('model_id', $user->id)->where('model_type', $morph)->get()->map(fn ($r) => (array) $r)->all()
                : [];
        }

        $t['companies'] = $this->rows('companies', ['user_id' => $uid]);
        $company = $this->ids($t['companies']);

        $t['sections'] = $this->rows('sections', ['company_id' => $company]);
        $section = $this->ids($t['sections']);
        $t['section_translations'] = $this->rows('section_translations', ['section_id' => $section]);

        $t['products'] = $this->rows('products', ['section_id' => $section]);
        $product = $this->ids($t['products']);
        $t['product_translations'] = $this->rows('product_translations', ['product_id' => $product]);
        $t['product_allergen'] = $this->rows('product_allergen', ['product_id' => $product]);

        $t['menus'] = $this->rows('menus', ['company_id' => $company]);
        $menu = $this->ids($t['menus']);
        $t['menu_sections'] = $this->rows('menu_sections', ['menu_id' => $menu]);
        $t['menu_items'] = $this->rows('menu_items', ['menu_id' => $menu]);

        $t['menu_scan_jobs'] = $this->rows('menu_scan_jobs', ['company_id' => $company, 'user_id' => $uid]);
        $t['translation_jobs'] = $this->rows('translation_jobs', ['company_id' => $company, 'user_id' => $uid]);
        $t['tvpik_screen_links'] = $this->rows('tvpik_screen_links', ['company_id' => $company, 'user_id' => $uid]);
        $t['sales_handoffs'] = $this->rows('sales_handoffs', [
            'company_id' => $company, 'sales_rep_user_id' => $uid, 'restaurant_user_id' => $uid,
        ]);
        $t['public_slug_redirects'] = $this->rows('public_slug_redirects', ['company_id' => $company, 'user_id' => $uid]);
        $t['user_lifecycle_emails'] = $this->rows('user_lifecycle_emails', ['user_id' => $uid]);
        $t['menu_pre_registrations'] = $this->rows('menu_pre_registrations', ['claimed_user_id' => $uid]);

        return $t;
    }

    /**
     * Filas de $table donde alguna de las columnas indicadas valga alguno de sus ids (OR).
     * Si la tabla no existe, o no hay columnas/ids válidos, devuelve [] (nunca "toda la tabla").
     *
     * @param  array<string, array<int, int|string>>  $filters  columna => ids
     * @return array<int, array<string, mixed>>
     */
    private function rows(string $table, array $filters): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $valid = [];
        foreach ($filters as $column => $ids) {
            if (count($ids) > 0 && Schema::hasColumn($table, $column)) {
                $valid[$column] = array_values($ids);
            }
        }
        if ($valid === []) {
            return [];
        }

        return DB::table($table)
            ->where(function ($q) use ($valid) {
                foreach ($valid as $column => $ids) {
                    $q->orWhereIn($column, $ids);
                }
            })
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function ids(array $rows): array
    {
        return array_values(array_filter(array_column($rows, 'id'), fn ($id) => $id !== null));
    }

    /** El log de auditoría nunca debe impedir el borrado ni ocultar el error original. */
    private function audit(string $level, string $message, array $context): void
    {
        try {
            Log::channel('account_deletions')->{$level}($message, $context);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
