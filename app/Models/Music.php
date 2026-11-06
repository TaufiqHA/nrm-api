<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Music extends Model
{
    protected $table = 'tb_music';

    protected $primaryKey = 'musicid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'songid',
        'musictitle',
        'musicartist',
        'musicfileid',
        'musicfilename',
        'musicmime',
        'musicduration',
        'musiccover',
        'is_active',
        'sort_order',
        'upload_status',
        'upload_error',
        'pending_file_path',
        'uploaded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'musicduration' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    /**
     * The attributes that are appended to array output.
     *
     * @var list<string>
     */
    protected $appends = [
        'cover_url',
        'stream_url',
    ];

    /**
     * URL publik gambar cover musik.
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (empty($this->musiccover)) {
            return null;
        }

        return Storage::disk('public')->url($this->musiccover);
    }

    /**
     * URL streaming proxy yang dipakai aplikasi mobile.
     */
    public function getStreamUrlAttribute(): string
    {
        return url('/api/music/'.$this->musicid.'/stream');
    }

    /**
     * Lagu katalog yang menjadi sumber metadata musik ini.
     *
     * @return BelongsTo<Song, $this>
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class, 'songid', 'songid');
    }

    /**
     * Scope a query to only include active music.
     *
     * @param  Builder<Music>  $query
     * @return Builder<Music>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include music whose file is available to stream.
     *
     * @param  Builder<Music>  $query
     * @return Builder<Music>
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->whereNotNull('musicfileid')
            ->where('musicfileid', '!=', '');
    }

    /**
     * Data musik sudah memiliki file di Google Drive.
     */
    public function hasDriveFile(): bool
    {
        return is_string($this->musicfileid)
            && $this->musicfileid !== '';
    }

    /**
     * Upload baru sedang berjalan dan belum selesai diproses.
     */
    public function isUploadInProgress(): bool
    {
        return in_array($this->upload_status, ['pending', 'processing'], true)
            && is_string($this->pending_file_path)
            && $this->pending_file_path !== '';
    }

    public function isReady(): bool
    {
        return $this->hasDriveFile();
    }

    /**
     * Selaraskan status musik lama yang file Drive-nya sudah tersedia.
     *
     * Record dengan upload baru yang masih berjalan tidak diubah agar
     * indikator "Mengunggah" tetap akurat.
     */
    public static function syncReadyStatuses(): int
    {
        return static::query()
            ->whereNotNull('musicfileid')
            ->where('musicfileid', '!=', '')
            ->where(function (Builder $query) {
                $query->whereNull('pending_file_path')
                    ->orWhere('pending_file_path', '');
            })
            ->where(function (Builder $query) {
                $query->whereNull('upload_status')
                    ->orWhere('upload_status', '!=', 'ready');
            })
            ->update([
                'upload_status' => 'ready',
                'upload_error' => null,
            ]);
    }
}
