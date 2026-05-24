<?php

return [
  'ingest_key' => env('PRE_ALTA_INGEST_KEY', ''),

  'retention_days' => (int) env('PRE_ALTA_RETENTION_DAYS', 20),

  'max_sections' => (int) env('PRE_ALTA_MAX_SECTIONS', 50),

  'max_products_per_section' => (int) env('PRE_ALTA_MAX_PRODUCTS_PER_SECTION', 100),

  'max_total_products' => (int) env('PRE_ALTA_MAX_PRODUCTS', 500),

  'image_download_timeout' => (int) env('PRE_ALTA_IMAGE_TIMEOUT', 30),

  'max_image_bytes' => (int) env('PRE_ALTA_MAX_IMAGE_BYTES', 8 * 1024 * 1024),

  'allowed_image_hosts' => array_filter(array_map('trim', explode(',', env('PRE_ALTA_ALLOWED_IMAGE_HOSTS', '')))),

  'allowed_image_mimes' => [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
  ],
];
