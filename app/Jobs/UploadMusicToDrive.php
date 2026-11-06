<?php

namespace App\Jobs;

use App\Models\Music;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UploadMusicToDrive implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 900;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(public int $musicId) {}

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('music-upload-'.$this->musicId))->expireAfter(1800),
        ];
    }

    public function handle(GoogleDriveService $drive): void
    {
        $music = Music::find($this->musicId);

        if ($music === null) {
            return;
        }

        if (! in_array($music->upload_status, ['pending', 'processing'], true)) {
            return;
        }

        $pendingPath = (string) $music->pending_file_path;

        if ($pendingPath === '' || ! Storage::disk('local')->exists($pendingPath)) {
            throw new RuntimeException('File sementara untuk unggahan musik tidak ditemukan.');
        }

        $music->forceFill([
            'upload_status' => 'processing',
            'upload_error' => null,
        ])->save();

        $absolutePath = Storage::disk('local')->path($pendingPath);
        $extension = pathinfo($pendingPath, PATHINFO_EXTENSION) ?: 'mp3';
        $uploaded = $drive->upload(
            $absolutePath,
            $this->safeFileName($music->musictitle, $extension),
            $music->musicmime ?: 'audio/mpeg',
        );

        $previousFileId = $music->musicfileid;

        $music->forceFill([
            'musicfileid' => $uploaded['id'],
            'musicfilename' => $uploaded['name'],
            'musicmime' => $uploaded['mimeType'] ?: ($music->musicmime ?: 'audio/mpeg'),
            'upload_status' => 'ready',
            'upload_error' => null,
            'pending_file_path' => null,
            'uploaded_at' => now(),
        ])->save();

        Storage::disk('local')->delete($pendingPath);

        if (is_string($previousFileId)
            && $previousFileId !== ''
            && $previousFileId !== $uploaded['id']) {
            try {
                $drive->delete($previousFileId);
            } catch (Throwable) {
                // File lama gagal dihapus tidak boleh membatalkan unggahan baru.
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        $music = Music::find($this->musicId);

        if ($music === null) {
            return;
        }

        if ($music->pending_file_path) {
            Storage::disk('local')->delete($music->pending_file_path);
        }

        $music->forceFill([
            'upload_status' => 'failed',
            'upload_error' => $exception->getMessage(),
            'pending_file_path' => null,
        ])->save();
    }

    private function safeFileName(string $title, string $extension): string
    {
        $base = preg_replace('/[^A-Za-z0-9 _-]/', '', $title) ?? '';
        $base = trim($base);

        if ($base === '') {
            $base = 'musik';
        }

        return $base.'.'.($extension ?: 'mp3');
    }
}
