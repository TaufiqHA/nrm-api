<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'songcategoryid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'songcategoryname',
        'icon',
        'icon_image',
        'sort_order',
    ];

    /**
     * URL absolut gambar ikon (null bila kategori memakai ikon bawaan atau otomatis).
     *
     * @var list<string>
     */
    protected $appends = [
        'icon_url',
    ];

    /**
     * Kategori baru otomatis diletakkan di urutan paling bawah.
     */
    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if ($category->sort_order === null) {
                $category->sort_order = (static::max('sort_order') ?? 0) + 1;
            }
        });
    }

    /**
     * URL publik gambar ikon kategori.
     */
    public function getIconUrlAttribute(): ?string
    {
        if (empty($this->icon_image)) {
            return null;
        }

        return Storage::disk('public')->url($this->icon_image);
    }

    /**
     * Get the songs for the category.
     *
     * @return HasMany<Song, $this>
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'songcategory', 'songcategoryid');
    }
}
