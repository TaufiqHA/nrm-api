<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\UploadMusicToDrive;
use App\Models\Category;
use App\Models\Music;
use App\Models\Song;
use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class MusicController extends Controller
{
    public function __construct(private readonly GoogleDriveService $drive) {}

    /**
     * Daftar lagu katalog beserta file musiknya.
     */
    public function index(Request $request): View
    {
        $query = Song::query()->with(['category', 'music']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('songtitle', 'like', "%{$search}%")
                    ->orWhere('songsinger', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('songcategory', $request->input('category'));
        }

        $songs = $query
            ->orderByRaw('CAST(songsinger AS UNSIGNED) ASC')
            ->orderBy('songtitle')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('sort_order')->orderBy('songcategoryname')->get();

        return view('admin.music.index', [
            'songs' => $songs,
            'categories' => $categories,
            'driveConfigured' => $this->drive->isConfigured(),
        ]);
    }

    /**
     * Unggah atau perbarui file musik untuk satu lagu katalog.
     */
    public function uploadForSong(Request $request, Song $song): RedirectResponse
    {
        if ($response = $this->ensureUploadWithinServerLimits($request)) {
            return $response;
        }

        $music = $song->music;
        $fileRequired = $music === null || ! is_string($music->musicfileid) || $music->musicfileid === '';

        $request->validate([
            'file' => [
                $fileRequired ? 'required' : 'nullable',
                'file',
                'mimes:mp3',
                'max:51200',
            ],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'file.required' => 'File MP3 wajib dipilih.',
            'file.mimes' => 'File musik harus berformat MP3.',
            'file.max' => 'Ukuran file MP3 maksimal 50 MB.',
            'file.uploaded' => 'File gagal diunggah. Ukuran file kemungkinan melebihi batas PHP server (upload_max_filesize).',
        ]);

        if ($request->hasFile('file')) {
            if ($music !== null && in_array($music->upload_status, ['pending', 'processing'], true)) {
                return back()->withInput()->with(
                    'error',
                    'File musik sedang diunggah. Tunggu sampai selesai sebelum mengganti file.',
                );
            }

            $file = $request->file('file');

            if ($music?->pending_file_path) {
                Storage::disk('local')->delete($music->pending_file_path);
            }

            $music ??= new Music([
                'songid' => $song->songid,
                'sort_order' => (Music::max('sort_order') ?? 0) + 1,
            ]);

            $music->fill([
                'musictitle' => $song->songtitle,
                'musicartist' => $song->songsinger,
                'musicmime' => $this->resolveMimeType($file),
                'is_active' => $request->boolean('is_active'),
                'upload_status' => 'pending',
                'upload_error' => null,
                'pending_file_path' => $this->storePendingFile($file),
            ]);
            $music->save();

            UploadMusicToDrive::dispatch($music->musicid);

            return redirect()->route('admin.music.index')->with(
                'success',
                'File musik lagu "'.$song->songtitle.'" sedang diunggah ke Google Drive di latar belakang.',
            );
        }

        if ($music === null) {
            return back()->withInput()->with('error', 'Pilih file MP3 terlebih dahulu.');
        }

        $music->update([
            'musictitle' => $song->songtitle,
            'musicartist' => $song->songsinger,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.music.index')->with('success', 'Data musik berhasil diperbarui.');
    }

    /**
     * Perbarui metadata dan/atau ganti file MP3 musik lama.
     */
    public function update(Request $request, Music $music): RedirectResponse
    {
        if ($response = $this->ensureUploadWithinServerLimits($request)) {
            return $response;
        }

        $validated = $this->validateMusic($request, false);

        $linkSongId = $request->input('songid');

        if ($linkSongId !== null && $linkSongId !== '') {
            $request->validate([
                'songid' => [
                    'integer',
                    'exists:tb_songs,songid',
                    Rule::unique('tb_music', 'songid')->ignore($music->musicid, 'musicid'),
                ],
            ], [
                'songid.exists' => 'Lagu yang dipilih tidak ditemukan.',
                'songid.unique' => 'Lagu tersebut sudah memiliki file musik.',
            ]);

            $linkedSong = Song::findOrFail($linkSongId);
            $validated['songid'] = $linkedSong->songid;
            $validated['musictitle'] = $linkedSong->songtitle;
            $validated['musicartist'] = $linkedSong->songsinger;
        }

        if ($request->hasFile('file')) {
            if (in_array($music->upload_status, ['pending', 'processing'], true)) {
                return back()->withInput()->with(
                    'error',
                    'File musik sedang diunggah. Tunggu sampai selesai sebelum mengganti file.',
                );
            }

            $file = $request->file('file');

            if ($music->pending_file_path) {
                Storage::disk('local')->delete($music->pending_file_path);
            }

            $music->fill([
                'musictitle' => $validated['musictitle'],
                'musicartist' => $validated['musicartist'] ?? $music->musicartist,
                'songid' => $validated['songid'] ?? $music->songid,
                'is_active' => $request->boolean('is_active'),
                'musicmime' => $this->resolveMimeType($file),
                'upload_status' => 'pending',
                'upload_error' => null,
                'pending_file_path' => $this->storePendingFile($file),
            ]);
            $music->save();

            UploadMusicToDrive::dispatch($music->musicid);

            return redirect()->route('admin.music.index')->with(
                'success',
                'Data musik diperbarui. File baru sedang diunggah ke Google Drive di latar belakang.',
            );
        }

        $music->update([
            'musictitle' => $validated['musictitle'],
            'musicartist' => $validated['musicartist'] ?? $music->musicartist,
            'songid' => $validated['songid'] ?? $music->songid,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.music.index')->with('success', 'Data musik berhasil diperbarui.');
    }

    /**
     * Hapus musik beserta file Drive dan cover lokalnya.
     */
    public function destroy(Music $music): RedirectResponse
    {
        if ($music->pending_file_path) {
            Storage::disk('local')->delete($music->pending_file_path);
        }

        if ($music->musicfileid) {
            try {
                $this->drive->delete($music->musicfileid);
            } catch (Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        if ($music->musiccover) {
            Storage::disk('public')->delete($music->musiccover);
        }

        $music->delete();

        return redirect()->route('admin.music.index')->with('success', 'Musik berhasil dihapus.');
    }

    /**
     * Aktifkan atau sembunyikan musik dari aplikasi.
     */
    public function updateStatus(Request $request, Music $music): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $music->is_active = (bool) $validated['is_active'];
        $music->save();

        return back()->with('success', $music->is_active
            ? 'Musik berhasil diaktifkan.'
            : 'Musik berhasil dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMusic(Request $request, bool $fileRequired): array
    {
        return $request->validate([
            'musictitle' => ['required', 'string', 'max:255'],
            'file' => [
                $fileRequired ? 'required' : 'nullable',
                'file',
                'mimes:mp3',
                'max:51200',
            ],
        ], [
            'musictitle.required' => 'Judul musik wajib diisi.',
            'musictitle.max' => 'Judul musik maksimal 255 karakter.',
            'file.required' => 'File MP3 wajib dipilih.',
            'file.mimes' => 'File musik harus berformat MP3.',
            'file.max' => 'Ukuran file MP3 maksimal 50 MB.',
            'file.uploaded' => 'File gagal diunggah. Ukuran file kemungkinan melebihi batas PHP server (upload_max_filesize).',
        ]);
    }

    private function resolveMimeType(UploadedFile $file): string
    {
        return $file->getMimeType() ?: 'audio/mpeg';
    }

    /**
     * Deteksi kegagalan upload dari batas PHP sebelum validasi berjalan.
     */
    private function ensureUploadWithinServerLimits(Request $request): ?RedirectResponse
    {
        $uploadMax = (string) ini_get('upload_max_filesize');
        $postMax = (string) ini_get('post_max_size');

        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength > 0 && $this->parseIniSize($postMax) > 0
            && $contentLength > $this->parseIniSize($postMax)) {
            return back()->withInput()->with(
                'error',
                'File gagal diunggah karena ukuran permintaan melebihi batas server '
                .'(post_max_size = '.$postMax.'). Naikkan post_max_size di php.ini.',
            );
        }

        $file = $request->file('file');
        if ($file !== null && ! $file->isValid() && $file->getError() === UPLOAD_ERR_INI_SIZE) {
            return back()->withInput()->with(
                'error',
                'File gagal diunggah karena ukurannya melebihi batas server '
                .'(upload_max_filesize = '.$uploadMax.'). Naikkan batas tersebut di php.ini.',
            );
        }

        return null;
    }

    private function parseIniSize(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * Simpan file unggahan ke disk privat agar bisa diproses queue.
     */
    private function storePendingFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'mp3');
        $path = $file->storeAs('music/pending', Str::uuid()->toString().'.'.$extension, 'local');

        if (! is_string($path)) {
            throw new RuntimeException('File musik gagal disimpan sementara.');
        }

        return $path;
    }
}
