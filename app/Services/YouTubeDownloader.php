<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class YouTubeDownloader
{
    /**
     * Download the audio track of a YouTube video as an MP3 file.
     */
    public function downloadAudio(string $url, string $destination, ?callable $onProgress = null): void
    {
        $this->download($url, $destination, [
            '-x',
            '--audio-format', 'mp3',
            '--audio-quality', '0',
        ], $onProgress);
    }

    /**
     * Download a YouTube video as an MP4 file.
     */
    public function downloadVideo(string $url, string $destination, ?callable $onProgress = null): void
    {
        $maxHeight = (int) config('youtube.max_height', 0);

        $format = $maxHeight > 0
            ? "bv*[height<={$maxHeight}]+ba/b[height<={$maxHeight}]/bv*+ba/b"
            : 'bv*+ba/b';

        $this->download($url, $destination, [
            '-f', $format,
            '--merge-output-format', 'mp4',
        ], $onProgress);
    }

    /**
     * Run yt-dlp with the given format options.
     *
     * @param  list<string>  $formatOptions
     */
    protected function download(string $url, string $destination, array $formatOptions, ?callable $onProgress = null): void
    {
        $directory = dirname($destination);

        File::ensureDirectoryExists($directory);

        if (is_file($destination)) {
            File::delete($destination);
        }

        $command = array_merge([
            (string) config('youtube.yt_dlp_path', 'yt-dlp'),
            '--no-playlist',
            '--newline',
            '--no-warnings',
            '--ffmpeg-location='.(string) config('youtube.ffmpeg_path', 'ffmpeg'),
        ], $formatOptions, [
            '-o', $destination,
            '--', $url,
        ]);

        $process = new Process($command, null, null, null, (float) config('youtube.timeout', 1800));

        try {
            $process->run(function (string $type, string $buffer) use ($onProgress): void {
                if ($onProgress === null || $type !== Process::OUT) {
                    return;
                }

                foreach (preg_split('/\R/', $buffer) ?: [] as $line) {
                    if (preg_match('/\[download\]\s+(\d+(?:\.\d+)?)%/', $line, $matches) !== 1) {
                        continue;
                    }

                    $onProgress((int) floor((float) $matches[1]));
                }
            });
        } catch (ProcessTimedOutException) {
            throw new RuntimeException('Unduhan melebihi batas waktu yang ditentukan.');
        }

        if (! $process->isSuccessful() || ! is_file($destination)) {
            $message = trim($process->getErrorOutput()) ?: trim($process->getOutput());

            throw new RuntimeException($message !== '' ? $message : 'yt-dlp gagal mengunduh media.');
        }
    }
}
