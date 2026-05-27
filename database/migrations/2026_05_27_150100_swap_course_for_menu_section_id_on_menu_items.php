<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SwapCourseForMenuSectionIdOnMenuItems extends Migration
{
    public function up()
    {
        // Idempotencia: en algunos MySQL la FK de menu_id depende del índice compuesto
        // (menu_id, course, position) y puede fallar al intentar eliminarlo.
        // Además, si el proceso se interrumpe, la columna puede existir ya.
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        // Asegura que existe un índice simple sobre menu_id para que la FK no dependa
        // del índice compuesto que vamos a modificar.
        try {
            DB::statement('ALTER TABLE menu_items ADD INDEX menu_items_menu_id_idx (menu_id)');
        } catch (\Throwable $e) {
            // Ignora si ya existe.
        }

        if (! Schema::hasColumn('menu_items', 'menu_section_id')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->unsignedBigInteger('menu_section_id')->nullable()->after('menu_id');
            });
        }

        // FK + índices (solo si no existen).
        try {
            DB::statement('ALTER TABLE menu_items ADD CONSTRAINT menu_items_menu_section_id_foreign FOREIGN KEY (menu_section_id) REFERENCES menu_sections(id) ON DELETE CASCADE');
        } catch (\Throwable $e) {
            // Ignora si ya existe.
        }
        try {
            DB::statement('CREATE INDEX menu_items_menu_section_id_position_index ON menu_items (menu_section_id, position)');
        } catch (\Throwable $e) {
            // Ignora si ya existe.
        }

        // Elimina el índice compuesto si existe.
        try {
            DB::statement('DROP INDEX menu_items_menu_id_course_position_index ON menu_items');
        } catch (\Throwable $e) {
            // Ignora si no existe o no se puede eliminar en este estado.
        }

        // Elimina course si existe.
        if (Schema::hasColumn('menu_items', 'course')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropColumn('course');
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        if (! Schema::hasColumn('menu_items', 'course')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->string('course', 16)->nullable()->after('menu_id');
            });
        }

        try {
            DB::statement('CREATE INDEX menu_items_menu_id_course_position_index ON menu_items (menu_id, course, position)');
        } catch (\Throwable $e) {
            // Ignora si ya existe.
        }

        // Quita FK + índice + columna menu_section_id si existen.
        try {
            DB::statement('ALTER TABLE menu_items DROP FOREIGN KEY menu_items_menu_section_id_foreign');
        } catch (\Throwable $e) {
            // Ignora si no existe.
        }
        try {
            DB::statement('DROP INDEX menu_items_menu_section_id_position_index ON menu_items');
        } catch (\Throwable $e) {
            // Ignora si no existe.
        }

        if (Schema::hasColumn('menu_items', 'menu_section_id')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropColumn('menu_section_id');
            });
        }
    }
}
