<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Services\GoogleDriveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MusicController extends Controller
{
    public function __construct(private readonly GoogleDriveService $drive) {}

    /**
     * Daftar musik aktif untuk aplikasi mobile.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Music::query()->active()->ready();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('musictitle', 'like', '%'.$search.'%')
                    ->orWhere('musicartist', 'like', '%'.$search.'%');
            });
        }

        $music = $query
            ->orderBy('sort_order')
            ->orderBy('musictitle')
            ->get();

        return response()->json([
            'data' => $music,
        ]);
    }

    /**
     * Proxy streaming file MP3 dari Google Drive.
     *
     * Header Range diteruskan ke Drive agar aplikasi bisa melakukan seek
     * tanpa mengunduh seluruh file terlebih dahulu.
     */
    public function stream(Request $request, Music $music): StreamedResponse
    {
        if (! $music->isReady()) {
            abort(404, 'File musik belum siap diputar.');
        }

        try {
            $stream = $this->drive->openStream(
                $music->musicfileid,
                $request->header('Range'),
            );
        } catch (Throwable $e) {
            abort(404, $e->getMessage());
        }

        $headers = array_merge($stream['headers'], [
            'Cache-Control' => 'private, max-age=0, no-store',
            'Content-Disposition' => 'inline; filename="'.addslashes($music->musicfilename ?: $music->musictitle.'.mp3').'"',
        ]);

        $body = $stream['body'];

        return response()->stream(function () use ($body) {
            @set_time_limit(0);
            ignore_user_abort(true);

            while (! $body->eof()) {
                if (connection_aborted()) {
                    break;
                }

                echo $body->read(8192);
                @flush();
            }

            $body->close();
        }, $stream['status'], $headers);
    }
}
