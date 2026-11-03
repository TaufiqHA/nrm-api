<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Binary Paths
    |--------------------------------------------------------------------------
    |
    | Lokasi executable yt-dlp dan ffmpeg yang dipakai untuk mengunduh media
    | dari YouTube. ffmpeg dibutuhkan untuk ekstraksi audio MP3 dan merge MP4.
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

];
