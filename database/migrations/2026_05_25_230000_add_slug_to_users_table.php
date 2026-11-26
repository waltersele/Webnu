<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddSlugToUsersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'slug')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('slug', 120)->nullable()->after('name');
            });
        }

        $this->backfill();

        // La columna se queda nullable a proposito. En Laravel 10 sin doctrine/dbal el
        // ->nullable(false)->change() fallaba en silencio (try/catch), asi que el esquema
        // real en produccion y en tests siempre fue nullable y la app crea usuarios sin slug
        // (se genera despues, ver User::resolveSlug). Laravel 11 hace el change() de forma
        // nativa y lo dejaria NOT NULL, rompiendo el alta de usuarios.

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('slug', 'users_slug_unique');
            });
        } catch (\Throwable $e) {
            // index might already exist on partial reruns
        }
    }

    public function down()
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_slug_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        if (Schema::hasColumn('users', 'slug')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }

    private function backfill(): void
    {
        $taken = DB::table('users')
            ->whereNotNull('slug')
            ->pluck('slug')
            ->flip()
            ->toArray();

        DB::table('users')->orderBy('id')->whereNull('slug')->cursor()->each(function ($u) use (&$taken) {
            $source = $u->name ?? '';
            if (trim((string) $source) === '') {
                $source = 'user-' . $u->id;
            }

            $base = Str::slug($source);
            if ($base === '') {
                $base = 'user-' . $u->id;
            }
            $base = substr($base, 0, 110);

            $candidate = $base;
            $i = 2;
            while (isset($taken[$candidate])) {
                $candidate = $base . '-' . $i++;
            }
            $taken[$candidate] = true;

            DB::table('users')->where('id', $u->id)->update(['slug' => $candidate]);
        });
    }
}
