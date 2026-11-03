<?php

use App\Jobs\DownloadSongMedia;
use App\Models\Category;
use App\Models\Song;
use App\Services\YouTubeDownloader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

function youtubeSong(): Song
{
    return Song::factory()->create([
        'songcategory' => Category::factory(),
        'songtitle' => 'Lagu Uji Unduh',
        'songurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'songyoutubeurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'download_status' => 'pending',
        'download_progress' => 0,
    ]);
}

test('download job stores both media files and updates the song urls', function () {
    Storage::fake('public');

    $song = youtubeSong();

    $this->mock(YouTubeDownloader::class, function (MockInterface $mock) {
        $mock->shouldReceive('downloadAudio')
            ->once()
            ->andReturnUsing(function (string $url, string $destination, callable $onProgress) {
                $onProgress(80);
                File::put($destination, 'audio-bytes');
            });

        $mock->shouldReceive('downloadVideo')
            ->once()
            ->andReturnUsing(function (string $url, string $destination, callable $onProgress) {
                $onProgress(100);
                File::put($destination, 'video-bytes');
            });
    });

    $job = new DownloadSongMedia($song->songid, $song->songurl);
    $job->handle(app(YouTubeDownloader::class));

    $song->refresh();

    expect($song->download_status)->toBe('completed')
        ->and($song->download_progress)->toBe(100)
        ->and($song->download_error)->toBeNull()
        ->and($song->downloaded_at)->not->toBeNull()
        ->and($song->songurl)->toContain("songs/{$song->songid}/audio.mp3")
        ->and($song->songvideourl)->toContain("songs/{$song->songid}/video.mp4")
        ->and($song->songyoutubeurl)->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    Storage::disk('public')->assertExists("songs/{$song->songid}/audio.mp3");
    Storage::disk('public')->assertExists("songs/{$song->songid}/video.mp4");
    Storage::disk('public')->assertMissing("songs/{$song->songid}/audio.partial.mp3");
});

test('download job keeps the old urls and marks failure when a download fails', function () {
    Storage::fake('public');

    $song = youtubeSong();
    $originalUrl = $song->songurl;

    $this->mock(YouTubeDownloader::class, function (MockInterface $mock) {
        $mock->shouldReceive('downloadAudio')
            ->once()
            ->andReturnUsing(function (string $url, string $destination) {
                File::put($destination, 'partial-audio');
                throw new RuntimeException('Video tidak tersedia.');
            });

        $mock->shouldReceive('downloadVideo')->never();
    });

    $job = new DownloadSongMedia($song->songid, $song->songurl);

    try {
        $job->handle(app(YouTubeDownloader::class));
    } catch (RuntimeException $exception) {
        $job->failed($exception);
    }

    $song->refresh();

    expect($song->download_status)->toBe('failed')
        ->and($song->download_error)->toBe('Video tidak tersedia.')
        ->and($song->songurl)->toBe($originalUrl)
        ->and($song->songvideourl)->toBeNull();

    Storage::disk('public')->assertMissing("songs/{$song->songid}/audio.partial.mp3");
    Storage::disk('public')->assertMissing("songs/{$song->songid}/audio.mp3");
});

test('download job aborts when the youtube url changed after being queued', function () {
    Storage::fake('public');

    $song = youtubeSong();
    $queuedUrl = $song->songyoutubeurl;
    $song->forceFill(['songyoutubeurl' => 'https://youtu.be/zzzzzzzzzzz'])->save();

    $this->mock(YouTubeDownloader::class, function (MockInterface $mock) {
        $mock->shouldReceive('downloadAudio')->never();
        $mock->shouldReceive('downloadVideo')->never();
    });

    $job = new DownloadSongMedia($song->songid, $queuedUrl);
    $job->handle(app(YouTubeDownloader::class));

    expect($song->fresh()->download_status)->toBe('pending');
});
