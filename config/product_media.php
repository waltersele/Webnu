<?php

return [
    'max_video_seconds' => (int) env('PRODUCT_MAX_VIDEO_SECONDS', 20),
    'max_video_kb' => (int) env('PRODUCT_MAX_VIDEO_KB', 15360),
    'allowed_video_mimes' => explode(',', env('PRODUCT_VIDEO_MIMES', 'mp4,webm,mov,quicktime')),

    /*
    | Vídeos ligeros para carta móvil y Smart TV (hardware limitado en TVPik).
    | Si FFmpeg está instalado, al subir se re-codifica a H.264 baseline 720p.
    */
    'tv_max_width' => (int) env('PRODUCT_TV_MAX_WIDTH', 1280),
    'tv_max_height' => (int) env('PRODUCT_TV_MAX_HEIGHT', 720),
    'tv_crf' => (int) env('PRODUCT_TV_CRF', 28),
    'tv_strip_audio' => env('PRODUCT_TV_STRIP_AUDIO', true),
    'ffmpeg_enabled' => env('PRODUCT_FFMPEG_ENABLED', true),
    'ffmpeg_path' => env('PRODUCT_FFMPEG_PATH', 'ffmpeg'),
];
