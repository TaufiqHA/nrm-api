<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
     * Scope a query to only include music whose file is ready to stream.
     *
     * @param  Builder<Music>  $query
     * @return Builder<Music>
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('upload_status', 'ready')
            ->whereNotNull('musicfileid')
            ->where('musicfileid', '!=', '');
    }

    public function isReady(): bool
    {
        return $this->upload_status === 'ready'
            && is_string($this->musicfileid)
            && $this->musicfileid !== '';
    }
}
