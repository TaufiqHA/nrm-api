<?php

namespace App\Models;

use Database\Factories\SongFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Song extends Model
{
    /** @use HasFactory<SongFactory> */
    use HasFactory;

    protected $table = 'tb_songs';

    protected $primaryKey = 'songid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'songtitle',
        'songsinger',
        'songurl',
        'songyoutubeurl',
        'songvideourl',
        'songlyric',
        'songcategory',
        'songnada',
        'songduration',
        'download_status',
        'download_progress',
        'download_error',
        'downloaded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
            'download_progress' => 'integer',
        ];
    }

    /**
     * Determine whether the given URL points to YouTube.
     */
    public static function isYouTubeLink(?string $url): bool
    {
        if (! is_string($url) || trim($url) === '') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        return $host === 'youtu.be'
            || $host === 'www.youtu.be'
            || $host === 'youtube.com'
            || str_ends_with($host, '.youtube.com');
    }

    /**
     * Determine whether a download is currently queued or running.
     */
    public function isDownloadInProgress(): bool
    {
        return in_array($this->download_status, ['pending', 'processing'], true);
    }

    /**
     * Get the category that owns the song.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'songcategory', 'songcategoryid');
    }

    /**
     * Get the nada associated with the song.
     *
     * @return BelongsTo<Nada, $this>
     */
    public function nada(): BelongsTo
    {
        return $this->belongsTo(Nada::class, 'songnada', 'nada');
    }

    /**
     * File musik Google Drive yang terhubung dengan lagu ini.
     *
     * @return HasOne<Music, $this>
     */
    public function music(): HasOne
    {
        return $this->hasOne(Music::class, 'songid', 'songid');
    }
}
