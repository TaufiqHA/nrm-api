<?php

namespace App\Models;

use Database\Factories\NadaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nada extends Model
{
    /** @use HasFactory<NadaFactory> */
    use HasFactory;

    protected $table = 'nadas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nada',
    ];

    /**
     * Get songs that use this nada.
     *
     * @return HasMany<Song, $this>
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'songnada', 'nada');
    }
}
