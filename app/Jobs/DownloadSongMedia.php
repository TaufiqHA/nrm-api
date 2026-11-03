<?php

namespace App\Jobs;

use App\Models\Song;
use App\Services\YouTubeDownloader;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadSongMedia implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Number of attempts before the job is marked as failed.
     */
    public int $tries = 2;

    /**
     * Seconds the worker may run this job before killing it.
     */
    public int $timeout;

    /**
     * Seconds the uniqueness lock is held.
     */
    public int $uniqueFor = 3600;

    public function __construct(
        public int $songId,
        public string $url,
    ) {
        $this->timeout = ((int) config('youtube.timeout', 1800) * 2) + 120;
    }

    public function uniqueId(): string
    {
        return $this->songId.':'.sha1($this->url);
    }

    public function handle(YouTubeDownloader $downloader): void
    {
        $song = Song::find($this->songId);

        if ($song === null) {
            return;
        }

        // Link berubah setelah job masuk queue — batalkan agar tidak menimpa data terbaru.
        if ($song->songurl !== $this->url || ! $song->isYouTubeUrl()) {
            return;
        }

        $diskName = (string) config('youtube.disk', 'public');
        $disk = Storage::disk($diskName);
        $folder = trim((string) config('youtube.folder', 'songs'), '/');
        $relativeDirectory = $folder.'/'.$song->songid;

        $disk->makeDirectory($relativeDirectory);

        $audioTemporary = $disk->path($relativeDirectory.'/audio.partial.mp3');
        $videoTemporary = $disk->path($relativeDirectory.'/video.partial.mp4');
        $audioFinal = $relativeDirectory.'/audio.mp3';
        $videoFinal = $relativeDirectory.'/video.mp4';

        $this->markProcessing($song);

        try {
            $downloader->downloadAudio(
                $this->url,
                $audioTemporary,
                fn (int $progress) => $this->reportProgress($song, (int) round($progress * 0.5)),
            );

            $downloader->downloadVideo(
                $this->url,
                $videoTemporary,
                fn (int $progress) => $this->reportProgress($song, 50 + (int) round($progress * 0.49)),
            );

            File::move($audioTemporary, $disk->path($audioFinal));
            File::move($videoTemporary, $disk->path($videoFinal));
        } catch (Throwable $exception) {
            File::delete([$audioTemporary, $videoTemporary]);

            throw $exception;
        }

        $song->forceFill([
            'songurl' => $disk->url($audioFinal),
            'songvideourl' => $disk->url($videoFinal),
            'download_status' => 'completed',
            'download_progress' => 100,
            'download_error' => null,
            'downloaded_at' => now(),
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        Song::whereKey($this->songId)->update([
            'download_status' => 'failed',
            'download_error' => $this->cleanErrorMessage($exception?->getMessage()),
        ]);
    }

    protected function markProcessing(Song $song): void
    {
        $song->forceFill([
            'download_status' => 'processing',
            'download_progress' => 0,
            'download_error' => null,
        ])->save();
    }

    protected function reportProgress(Song $song, int $progress): void
    {
        $progress = max(0, min(99, $progress));

        if ($progress < 5 || $progress % 5 !== 0) {
            return;
        }

        Song::whereKey($song->songid)->update([
            'download_status' => 'processing',
            'download_progress' => $progress,
        ]);
    }

    protected function cleanErrorMessage(?string $message): string
    {
        $message = trim((string) $message);

        if ($message === '') {
            return 'Terjadi kesalahan saat mengunduh media dari YouTube.';
        }

        return mb_substr($message, 0, 2000);
    }
}
