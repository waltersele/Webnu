<?php

return [

    'enabled' => env('MEASUREMENT_ENABLED', false),

    'brand' => env('MEASUREMENT_BRAND', 'webnu'),

    'cookie_banner_enabled' => env('MEASUREMENT_COOKIE_BANNER', true),

    'load_google_before_consent' => env('MEASUREMENT_LOAD_GOOGLE_BEFORE_CONSENT', true),

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),

    'gtag_measurement_id' => env('GTAG_MEASUREMENT_ID'),

    'gtm_container_id' => env('GTM_CONTAINER_ID'),

    'clarity_project_id' => env('CLARITY_PROJECT_ID'),

    'meta_pixel_id' => env('META_PIXEL_ID'),

    'linkedin_partner_id' => env('LINKEDIN_PARTNER_ID'),

    'plausible_domain' => env('PLAUSIBLE_DOMAIN', 'webnu.es'),

    'plausible_script_url' => env('PLAUSIBLE_SCRIPT_URL', '/stats/js/script.js'),

    'plausible_api_url' => env('PLAUSIBLE_API_URL', '/stats/api/event'),

    'plausible_upstream_url' => env('PLAUSIBLE_UPSTREAM_URL', env('PLAUSIBLE_ORIGIN', 'https://plausible.io')),
];
