<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Exception as GoogleServiceException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Klien tipis untuk operasi Google Drive yang dibutuhkan menu Musik:
 * unggah MP3, hapus file, dan buka stream unduhan.
 */
class GoogleDriveService
{
    private ?Client $client = null;

    private ?Drive $drive = null;

    /**
     * Periksa apakah konfigurasi dasar sudah diisi.
     */
    public function isConfigured(): bool
    {
        foreach (['client_id', 'client_secret', 'refresh_token', 'folder_id'] as $key) {
            if (trim((string) config('services.google_drive.'.$key)) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Unggah file ke folder Google Drive tujuan.
     *
     * @return array{id: string, name: string, mimeType: string|null}
     */
    public function upload(string $absolutePath, string $name, string $mimeType): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException('File yang akan diunggah tidak ditemukan.');
        }

        $contents = file_get_contents($absolutePath);

        if ($contents === false) {
            throw new RuntimeException('File MP3 tidak dapat dibaca.');
        }

        $metadata = new DriveFile;
        $metadata->setName($name);
        $metadata->setParents([$this->folderId()]);

        try {
            $created = $this->drive()->files->create($metadata, [
                'data' => $contents,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'supportsAllDrives' => true,
                'fields' => 'id,name,mimeType',
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException($this->friendlyError($e, 'Gagal mengunggah file ke Google Drive.'), 0, $e);
        }

        return [
            'id' => (string) $created->getId(),
            'name' => (string) ($created->getName() ?? $name),
            'mimeType' => $created->getMimeType(),
        ];
    }

    /**
     * Hapus file dari Google Drive. File yang sudah tidak ada dianggap sukses.
     */
    public function delete(string $fileId): void
    {
        try {
            $this->drive()->files->delete($fileId, ['supportsAllDrives' => true]);
        } catch (GoogleServiceException $e) {
            if ((int) $e->getCode() === 404) {
                return;
            }

            throw new RuntimeException($this->friendlyError($e, 'Gagal menghapus file dari Google Drive.'), 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException($this->friendlyError($e, 'Gagal menghapus file dari Google Drive.'), 0, $e);
        }
    }

    /**
     * Buka stream file Drive beserta header responsnya.
     *
     * @return array{status: int, headers: array<string, string>, body: StreamInterface}
     */
    public function openStream(string $fileId, ?string $range = null): array
    {
        $headers = [];

        if ($range !== null && $range !== '') {
            $headers['Range'] = $range;
        }

        try {
            $response = Http::withToken($this->accessToken())
                ->withOptions(['stream' => true])
                ->withHeaders($headers)
                ->get('https://www.googleapis.com/drive/v3/files/'.$fileId, [
                    'alt' => 'media',
                    'supportsAllDrives' => 'true',
                ]);
        } catch (\Throwable $e) {
            throw new RuntimeException($this->friendlyError($e, 'Gagal mengambil file dari Google Drive.'), 0, $e);
        }

        $status = $response->status();

        if ($status >= 400) {
            throw new RuntimeException(
                $status === 404
                    ? 'File musik tidak ditemukan di Google Drive.'
                    : 'Google Drive menolak permintaan file (HTTP '.$status.').',
            );
        }

        $responseHeaders = [];

        foreach (['Content-Type', 'Content-Length', 'Content-Range', 'Accept-Ranges'] as $key) {
            $value = $response->header($key);

            if ($value !== '') {
                $responseHeaders[$key] = $value;
            }
        }

        if (! isset($responseHeaders['Content-Type'])) {
            $responseHeaders['Content-Type'] = 'audio/mpeg';
        }

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => $response->toPsrResponse()->getBody(),
        ];
    }

    /**
     * Access token service account dengan cache agar tidak meminta terus-menerus.
     */
    private function accessToken(): string
    {
        $cached = Cache::get('google_drive_access_token');

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $client = $this->client();

        try {
            $token = $client->fetchAccessTokenWithRefreshToken(
                $this->requiredConfig('refresh_token', 'GOOGLE_DRIVE_REFRESH_TOKEN'),
            );
        } catch (\Throwable $e) {
            throw new RuntimeException($this->friendlyError($e, 'Gagal mengautentikasi ke Google Drive.'), 0, $e);
        }

        if (! is_array($token) || empty($token['access_token'])) {
            throw new RuntimeException(
                'Google Drive menolak refresh token. Periksa GOOGLE_DRIVE_CLIENT_ID, '
                .'GOOGLE_DRIVE_CLIENT_SECRET, dan GOOGLE_DRIVE_REFRESH_TOKEN.',
            );
        }

        $client->setAccessToken($token);

        $ttl = max(60, ((int) ($token['expires_in'] ?? 3600)) - 60);
        Cache::put('google_drive_access_token', $token['access_token'], $ttl);

        return (string) $token['access_token'];
    }

    private function drive(): Drive
    {
        if ($this->drive instanceof Drive) {
            return $this->drive;
        }

        $client = $this->client();
        $client->setAccessToken($this->accessToken());

        return $this->drive = new Drive($client);
    }

    private function client(): Client
    {
        if ($this->client instanceof Client) {
            return $this->client;
        }

        $client = new Client;
        $client->setClientId($this->requiredConfig('client_id', 'GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret($this->requiredConfig('client_secret', 'GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');

        return $this->client = $client;
    }

    /**
     * Ambil nilai konfigurasi wajib dari `.env`.
     */
    private function requiredConfig(string $key, string $envName): string
    {
        $value = trim((string) config('services.google_drive.'.$key));

        if ($value === '') {
            throw new RuntimeException('Konfigurasi Google Drive belum lengkap. Isi '.$envName.' di file .env.');
        }

        return $value;
    }

    private function folderId(): string
    {
        $folderId = trim((string) config('services.google_drive.folder_id'));

        // Dukung tempelan URL folder Google Drive, bukan hanya ID mentah.
        if (preg_match('#/folders/([A-Za-z0-9_-]+)#', $folderId, $matches) === 1) {
            $folderId = $matches[1];
        }

        if ($folderId === '') {
            throw new RuntimeException('Folder Google Drive belum diatur. Isi GOOGLE_DRIVE_FOLDER_ID.');
        }

        if ($folderId !== 'root' && preg_match('/^[A-Za-z0-9_-]{20,}$/', $folderId) !== 1) {
            throw new RuntimeException(
                'GOOGLE_DRIVE_FOLDER_ID harus berupa ID folder Google Drive, bukan nama folder. '
                .'Buka folder di Drive lalu salin ID dari URL (bagian setelah /folders/).',
            );
        }

        return $folderId;
    }

    /**
     * Ambil pesan asli dari Google bila ada agar admin mudah memperbaiki konfigurasi.
     */
    private function friendlyError(\Throwable $e, string $fallback): string
    {
        $message = trim($e->getMessage());

        if ($message === '') {
            return $fallback;
        }

        if (str_contains($message, 'invalid_grant') || str_contains($message, 'Invalid JWT')) {
            return $fallback.' Periksa kembali file service account dan waktu server.';
        }

        return $fallback.' '.$message;
    }
}
