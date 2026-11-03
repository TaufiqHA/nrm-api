<?php

use App\Services\YouTubeDownloader;

test('ffmpeg location uses the containing directory when given a binary path', function () {
    $binary = PHP_BINARY;

    expect(YouTubeDownloader::resolveFfmpegLocation($binary))
        ->toBe(dirname($binary));
});

test('ffmpeg location keeps a directory path as is', function () {
    $directory = dirname(PHP_BINARY);

    expect(YouTubeDownloader::resolveFfmpegLocation($directory))
        ->toBe($directory);
});

test('ffmpeg location ignores bare command names so yt-dlp can use PATH', function () {
    expect(YouTubeDownloader::resolveFfmpegLocation('ffmpeg'))->toBeNull()
        ->and(YouTubeDownloader::resolveFfmpegLocation(''))->toBeNull()
        ->and(YouTubeDownloader::resolveFfmpegLocation(null))->toBeNull();
});
