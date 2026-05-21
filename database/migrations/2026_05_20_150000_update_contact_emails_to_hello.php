<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateContactEmailsToHello extends Migration
{
    private const NEW_EMAIL = 'hello@webnu.es';

    /** @var array<string, list<string>> */
    private const LEGACY_BY_KEY = [
        'contact_leads_email' => ['info@webnu.es', 'hola@webnu.es'],
        'contact_suggestions_email' => ['hola@webnu.es', 'info@webnu.es'],
        'contact_public_email' => ['hola@webnu.es', 'info@webnu.es'],
    ];

    public function up()
    {
        if (! Schema::hasTable('platform_settings')) {
            return;
        }

        foreach (self::LEGACY_BY_KEY as $key => $legacyValues) {
            DB::table('platform_settings')
                ->where('key', $key)
                ->whereIn('value', $legacyValues)
                ->update(['value' => self::NEW_EMAIL, 'updated_at' => now()]);
        }
    }

    public function down()
    {
        // No revert: el cambio de email de contacto es intencional.
    }
}
