<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private const TEMPLATE_MAP = [
        'oriental' => 'japo',
        'basic' => 'pasion',
        'visual' => 'catalogo',
        'atelier' => 'maison',
        'bistro' => 'catalogo',
    ];

    public function up(): void
    {
        foreach (self::TEMPLATE_MAP as $from => $to) {
            DB::table('companies')
                ->where('template', $from)
                ->update(['template' => $to]);
        }
    }

    public function down(): void
    {
        foreach (self::TEMPLATE_MAP as $from => $to) {
            DB::table('companies')
                ->where('template', $to)
                ->update(['template' => $from]);
        }
    }
};
