<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\YouTubeDownloader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FixSongVideos extends Command
{
    /**
     * @var string
     */
    protected $signature = 'songs:fix-videos
                            {--id=* : Batasi pemrosesan ke songid tertentu}
                            {--force : Proses ulang meski video sudah kompatibel}';

    /**
     * @var string
     */
    protected $description = 'Pastikan video MP4 lagu memakai H.264 + AAC agar kompatibel dengan Android';

    public function handle(YouTubeDownloader $downloader): int
    {
        $diskName = (string) config('youtube.disk', 'public');
        $folder = trim((string) config('youtube.folder', 'songs'), '/');
        $disk = Storage::disk($diskName);

        $query = Song::query()->whereNotNull('songvideourl')->orderBy('songid');
        $ids = array_values(array_filter(
            array_map('intval', (array) $this->option('id')),
            fn (int $id): bool => $id > 0,
        ));

        if ($ids !== []) {
            $query->whereIn('songid', $ids);
        }

        $songs = $query->get();
        $fixed = 0;
        $skipped = 0;
        $failed = 0;

        $this->info("Memeriksa {$songs->count()} lagu...");

        foreach ($songs as $song) {
            $relativePath = $folder.'/'.$song->songid.'/video.mp4';
            $absolutePath = $disk->path($relativePath);

            if (! is_file($absolutePath)) {
                $this->line("  - [{$song->songid}] {$song->songtitle}: video tidak ada, dilewati.");
                $skipped++;

                continue;
            }

            try {
                $changed = $downloader->ensureAndroidCompatibleVideo(
                    $absolutePath,
                    skipIfCompatible: ! $this->option('force'),
                );

                if ($changed) {
                    $this->info("  ✓ [{$song->songid}] {$song->songtitle}: video diperbaiki.");
                    $fixed++;
                } else {
                    $this->line("  - [{$song->songid}] {$song->songtitle}: sudah kompatibel.");
                    $skipped++;
                }
            } catch (Throwable $exception) {
                $failed++;
                $this->error("  ✗ [{$song->songid}] {$song->songtitle}: {$exception->getMessage()}");
                Log::error('Gagal memperbaiki video lagu.', [
                    'songid' => $song->songid,
                    'path' => $absolutePath,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Selesai: {$fixed} diperbaiki, {$skipped} dilewati, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
