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

test('binary resolver derives ffprobe from a configured ffmpeg file', function () {
    $binary = PHP_BINARY;

    expect(YouTubeDownloader::resolveBinary($binary, 'ffprobe'))
        ->toBe(dirname($binary).DIRECTORY_SEPARATOR.'ffprobe');
});

test('binary resolver derives ffprobe from a configured directory', function () {
    $directory = dirname(PHP_BINARY);

    expect(YouTubeDownloader::resolveBinary($directory, 'ffprobe'))
        ->toBe($directory.DIRECTORY_SEPARATOR.'ffprobe');
});

test('binary resolver keeps bare command names discoverable via PATH', function () {
    expect(YouTubeDownloader::resolveBinary('ffmpeg', 'ffprobe'))->toBe('ffprobe')
        ->and(YouTubeDownloader::resolveBinary('', 'ffmpeg'))->toBe('ffmpeg');
});

test('android compatible format prefers h264 and aac', function () {
    $downloader = new YouTubeDownloader();
    $method = new ReflectionMethod($downloader, 'buildVideoFormat');

    $format = $method->invoke($downloader, 720, true);

    expect($format)
        ->toContain('vcodec^=avc1')
        ->toContain('acodec^=mp4a')
        ->toContain('height<=720');
});

test('format without android compatibility keeps the original behavior', function () {
    $downloader = new YouTubeDownloader();
    $method = new ReflectionMethod($downloader, 'buildVideoFormat');

    expect($method->invoke($downloader, 720, false))
        ->toBe('bv*[height<=720]+ba/b[height<=720]/bv*+ba/b')
        ->and($method->invoke($downloader, 0, false))
        ->toBe('bv*+ba/b');
});
