<?php

return [
    'max_video_seconds' => (int) env('PRODUCT_MAX_VIDEO_SECONDS', 30),
    'max_video_kb' => (int) env('PRODUCT_MAX_VIDEO_KB', 25600),
    'allowed_video_mimes' => explode(',', env('PRODUCT_VIDEO_MIMES', 'mp4,webm,mov,quicktime')),
];
