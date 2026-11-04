<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

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
        $androidCompatible = (bool) config('youtube.android_compatible', true);

        $this->download($url, $destination, [
            '-f', $this->buildVideoFormat($maxHeight, $androidCompatible),
            '--merge-output-format', 'mp4',
        ], $onProgress);

        if ($androidCompatible) {
            $this->ensureAndroidCompatibleVideo($destination);
        }
    }

    /**
     * Bangun format yt-dlp yang memprioritaskan H.264 + AAC.
     */
    protected function buildVideoFormat(int $maxHeight, bool $androidCompatible): string
    {
        $height = $maxHeight > 0 ? "[height<={$maxHeight}]" : '';

        if (! $androidCompatible) {
            return $maxHeight > 0
                ? "bv*{$height}+ba/b{$height}/bv*+ba/b"
                : 'bv*+ba/b';
        }

        // Urutan fallback:
        // 1. H.264 + AAC sesuai batas tinggi.
        // 2. File MP4 progresif H.264 (sudah menyatu).
        // 3. H.264 + audio apa pun (akan ditranscode bila perlu).
        // 4. Format terbaik apa pun sebagai upaya terakhir.
        $formats = [
            "bv*[vcodec^=avc1]{$height}+ba[acodec^=mp4a]",
            "b[ext=mp4][vcodec^=avc1]{$height}",
            "bv*[vcodec^=avc1]{$height}+ba",
        ];

        if ($maxHeight > 0) {
            $formats[] = "bv*{$height}+ba/b{$height}";
        }

        $formats[] = 'bv*+ba/b';

        return implode('/', $formats);
    }

    /**
     * Pastikan video memakai H.264 + AAC dan moov atom berada di awal file.
     *
     * @return bool True bila file diubah.
     */
    public function ensureAndroidCompatibleVideo(string $path, bool $skipIfCompatible = false): bool
    {
        if (! is_file($path)) {
            throw new RuntimeException('File video tidak ditemukan: '.$path);
        }

        $videoCodec = $this->probeCodec('v', $path);
        $audioCodec = $this->probeCodec('a', $path);
        $videoCompatible = $videoCodec === 'h264';
        $audioCompatible = $audioCodec === null || $audioCodec === 'aac';
        $compatible = $videoCompatible && $audioCompatible;

        if ($compatible && $skipIfCompatible) {
            return false;
        }

        $temporary = $path.'.compat.mp4';
        File::delete($temporary);

        $action = 'remux';

        try {
            if ($videoCompatible && $audioCompatible) {
                // Sudah H.264 + AAC: cukup pindahkan moov atom ke awal file.
                $this->runFfmpeg([
                    '-y',
                    '-i', $path,
                    '-c', 'copy',
                    '-movflags', '+faststart',
                    $temporary,
                ]);
            } elseif ($videoCompatible) {
                // Video sudah H.264: hanya audio yang perlu diubah ke AAC.
                $action = 'audio_transcode';
                $this->runFfmpeg([
                    '-y',
                    '-i', $path,
                    '-c:v', 'copy',
                    '-c:a', 'aac',
                    '-b:a', (string) config('youtube.aac_bitrate', '160k'),
                    '-movflags', '+faststart',
                    $temporary,
                ]);
            } else {
                $action = 'transcode';
                $maxHeight = (int) config('youtube.max_height', 0);
                $videoFilters = [];

                if ($maxHeight > 0) {
                    // Batasi tinggi sekaligus jaga rasio dan nilai genap untuk H.264.
                    $videoFilters[] = "scale=-2:'min({$maxHeight},ih)'";
                }

                $arguments = [
                    '-y',
                    '-i', $path,
                    '-c:v', 'libx264',
                    '-preset', (string) config('youtube.video_preset', 'veryfast'),
                    '-crf', (string) config('youtube.video_crf', 23),
                    '-profile:v', 'high',
                    '-pix_fmt', 'yuv420p',
                ];

                if ($videoFilters !== []) {
                    $arguments[] = '-vf';
                    $arguments[] = implode(',', $videoFilters);
                }

                array_push(
                    $arguments,
                    '-c:a', 'aac',
                    '-b:a', (string) config('youtube.aac_bitrate', '160k'),
                    '-movflags', '+faststart',
                    $temporary,
                );

                $this->runFfmpeg($arguments);
            }

            if (! is_file($temporary)) {
                throw new RuntimeException('ffmpeg tidak menghasilkan file video baru.');
            }
        } catch (Throwable $exception) {
            File::delete($temporary);

            throw $exception;
        }

        File::delete($path);
        File::move($temporary, $path);

        Log::info('Video lagu diubah agar kompatibel Android.', [
            'path' => $path,
            'video_codec' => $videoCodec,
            'audio_codec' => $audioCodec,
            'action' => $action,
        ]);

        return true;
    }

    /**
     * Deteksi codec stream pertama dari tipe yang diminta ('v' atau 'a').
     */
    protected function probeCodec(string $type, string $path): ?string
    {
        $process = new Process([
            self::resolveBinary(config('youtube.ffmpeg_path'), 'ffprobe'),
            '-v', 'error',
            '-select_streams', $type.':0',
            '-show_entries', 'stream=codec_name',
            '-of', 'default=nw=1:nk=1',
            $path,
        ], null, null, null, 60.0);

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            throw new RuntimeException('Pemeriksaan codec video melebihi batas waktu.');
        }

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput());

            throw new RuntimeException(
                $message !== '' ? $message : 'ffprobe gagal memeriksa codec video.'
            );
        }

        $codec = strtolower(trim((string) strtok($process->getOutput(), "\r\n")));

        return $codec !== '' ? $codec : null;
    }

    /**
     * Jalankan ffmpeg dengan argumen yang diberikan.
     *
     * @param  list<string>  $arguments
     */
    protected function runFfmpeg(array $arguments): void
    {
        $process = new Process(
            array_merge(
                [self::resolveBinary(config('youtube.ffmpeg_path'), 'ffmpeg')],
                $arguments,
            ),
            null,
            null,
            null,
            (float) config('youtube.timeout', 1800),
        );

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            throw new RuntimeException('Pemrosesan video melebihi batas waktu.');
        }

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput()) ?: trim($process->getOutput());

            throw new RuntimeException(
                $message !== '' ? $message : 'ffmpeg gagal memproses video.'
            );
        }
    }

    /**
     * Resolve path executable ffmpeg/ffprobe dari konfigurasi.
     */
    public static function resolveBinary(?string $configuredPath, string $name): string
    {
        $configuredPath = trim((string) $configuredPath);

        if ($configuredPath === '') {
            return $name;
        }

        if (is_dir($configuredPath)) {
            return rtrim($configuredPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
        }

        if (is_file($configuredPath) || str_contains($configuredPath, DIRECTORY_SEPARATOR)) {
            return dirname($configuredPath).DIRECTORY_SEPARATOR.$name;
        }

        // Nama command tanpa path; biarkan ditemukan melalui PATH.
        return $name;
    }

    /**
     * Resolve the value for yt-dlp's --ffmpeg-location option.
     *
     * yt-dlp expects a directory containing both ffmpeg and ffprobe. When the
     * configured value points to the ffmpeg binary, use its parent directory.
     * Bare command names are left for yt-dlp to resolve through PATH.
     */
    public static function resolveFfmpegLocation(?string $configuredPath): ?string
    {
        $configuredPath = trim((string) $configuredPath);

        if ($configuredPath === '') {
            return null;
        }

        if (is_file($configuredPath)) {
            return dirname($configuredPath);
        }

        if (is_dir($configuredPath)) {
            return $configuredPath;
        }

        return null;
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

        $command = [
            (string) config('youtube.yt_dlp_path', 'yt-dlp'),
            '--no-playlist',
            '--newline',
            '--no-warnings',
        ];

        $ffmpegLocation = self::resolveFfmpegLocation(config('youtube.ffmpeg_path'));

        if ($ffmpegLocation !== null) {
            $command[] = '--ffmpeg-location='.$ffmpegLocation;
        }

        $command = array_merge($command, $formatOptions, [
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
