<?php

return [
    'enabled' => env('MEASUREMENT_ENABLED', false),

    'cookie_banner_enabled' => env('MEASUREMENT_COOKIE_BANNER', true),

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),

    'gtag_measurement_id' => env('GTAG_MEASUREMENT_ID'),

    'gtm_container_id' => env('GTM_CONTAINER_ID'),

    'clarity_project_id' => env('CLARITY_PROJECT_ID'),
];
