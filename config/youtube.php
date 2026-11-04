<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Binary Paths
    |--------------------------------------------------------------------------
    |
    | Lokasi executable yt-dlp dan ffmpeg yang dipakai untuk mengunduh media
    | dari YouTube. FFMPEG_PATH boleh berupa direktori (contoh: /usr/bin) atau
    | path binary (contoh: /usr/bin/ffmpeg) — yt-dlp membutuhkan ffmpeg dan
    | ffprobe di direktori yang sama. Nama command tanpa path akan dicari
    | melalui PATH.
    |
    */

    'yt_dlp_path' => env('YT_DLP_PATH', 'yt-dlp'),

    'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),

    /*
    |--------------------------------------------------------------------------
    | Download Storage
    |--------------------------------------------------------------------------
    |
    | Disk dan folder tempat hasil unduhan disimpan. File akan ditempatkan di
    | "{folder}/{songid}/audio.mp3" dan "{folder}/{songid}/video.mp4".
    |
    */

    'disk' => env('YOUTUBE_DISK', 'public'),

    'folder' => env('YOUTUBE_FOLDER', 'songs'),

    /*
    |--------------------------------------------------------------------------
    | Process Timeout
    |--------------------------------------------------------------------------
    |
    | Batas waktu (detik) untuk satu proses unduhan. Video berdurasi panjang
    | bisa membutuhkan waktu lebih lama dari batas default PHP.
    |
    */

    'timeout' => (int) env('YOUTUBE_TIMEOUT', 1800),

    /*
    |--------------------------------------------------------------------------
    | Video Quality
    |--------------------------------------------------------------------------
    |
    | Batas tinggi video MP4 yang diunduh. Gunakan 0 untuk kualitas terbaik.
    |
    */

    'max_height' => (int) env('YOUTUBE_MAX_HEIGHT', 720),

    /*
    |--------------------------------------------------------------------------
    | Kompatibilitas Android
    |--------------------------------------------------------------------------
    |
    | Bila aktif, video MP4 dipaksa memakai H.264 + AAC agar dapat diputar di
    | sebagian besar perangkat Android. Video lama yang tidak kompatibel dapat
    | diperbaiki dengan perintah `php artisan songs:fix-videos`.
    |
    */

    'android_compatible' => (bool) env('YOUTUBE_ANDROID_COMPATIBLE', true),

    'video_preset' => env('YOUTUBE_VIDEO_PRESET', 'veryfast'),

    'video_crf' => (int) env('YOUTUBE_VIDEO_CRF', 23),

    'aac_bitrate' => env('YOUTUBE_AAC_BITRATE', '160k'),

];
