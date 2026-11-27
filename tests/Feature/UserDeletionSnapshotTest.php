<?php

namespace Tests\Feature;

use App\Company;
use App\Services\Platform\UserDeletionService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UserDeletionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCompany(): array
    {
        $user = User::factory()->create(['email' => 'cliente@example.com']);
        $company = Company::create([
            'name' => 'Cafetería Test',
            'slug' => 'cafeteria-test-' . $user->id,
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        return [$user, $company];
    }

    public function test_guarda_copia_verificada_antes_de_borrar(): void
    {
        [$user, $company] = $this->userWithCompany();
        $id = $user->id;

        app(UserDeletionService::class)->delete($user, $user);

        $this->assertDatabaseMissing('users', ['id' => $id]);

        $files = File::glob(config('platform.deleted_users_path') . "/user-{$id}-*.json");
        $this->assertCount(1, $files);

        $data = json_decode(file_get_contents($files[0]), true);
        $this->assertSame('cliente@example.com', $data['tables']['users'][0]['email']);
        $this->assertSame($company->id, $data['tables']['companies'][0]['id']);
        $this->assertSame(1, $data['rows']['companies']);
        $this->assertTrue($data['meta']['self_service']);
    }

    public function test_deja_registro_de_auditoria(): void
    {
        [$user] = $this->userWithCompany();

        app(UserDeletionService::class)->delete($user, $user);

        $log = file_get_contents(config('logging.channels.account_deletions.path'));
        $this->assertStringContainsString('user_deletion_started', $log);
        $this->assertStringContainsString('user_deleted', $log);
        $this->assertStringContainsString('cliente@example.com', $log);
    }

    public function test_si_no_se_puede_guardar_la_copia_no_se_borra_nada(): void
    {
        [$user, $company] = $this->userWithCompany();

        // La "carpeta" es en realidad un fichero: imposible crear/escribir el snapshot.
        $blocker = sys_get_temp_dir() . '/webnu-blocker-' . uniqid();
        file_put_contents($blocker, 'x');
        config(['platform.deleted_users_path' => $blocker . '/sub']);

        try {
            app(UserDeletionService::class)->delete($user, $user);
            $this->fail('Debía lanzar RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('NO se ha eliminado', $e->getMessage());
        } finally {
            @unlink($blocker);
        }

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('companies', ['id' => $company->id]);
        $log = file_get_contents(config('logging.channels.account_deletions.path'));
        $this->assertStringContainsString('user_deletion_aborted_no_snapshot', $log);
    }

    public function test_superadmin_no_genera_copia_ni_se_borra(): void
    {
        $this->seed(\Database\Seeders\PlatformRolesSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        try {
            app(UserDeletionService::class)->delete($admin, $admin);
            $this->fail('Debía lanzar InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            // esperado
        }

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertSame([], File::glob(config('platform.deleted_users_path') . '/*.json'));
    }
}
