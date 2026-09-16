<?php

namespace App\Models;

use Database\Factories\SongFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'songcategory',
        'songnada',
        'songduration',
    ];

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
}
