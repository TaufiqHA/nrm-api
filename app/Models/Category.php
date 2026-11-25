<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
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
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
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
     * Kategori baru otomatis diletakkan di urutan paling bawah dan langsung
     * mendapatkan tiga slot iklan (atas, pop-up, dan bawah).
     */
    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if ($category->sort_order === null) {
                $category->sort_order = (static::max('sort_order') ?? 0) + 1;
            }
        });

        static::created(function (Category $category) {
            foreach (CategoryAd::SLOTS as $slot) {
                $category->adSlots()->create([
                    'slot' => $slot,
                    'type' => 'image',
                    'active' => 'Y',
                ]);
            }
        });

        static::deleting(function (Category $category) {
            // Cascade delete di database tidak memicu event model, sehingga
            // berkas gambar slot iklan harus dibersihkan di sini.
            foreach ($category->adSlots as $adSlot) {
                $adSlot->deleteStoredImage();
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

    /**
     * Get the ad slots for the category.
     *
     * @return HasMany<CategoryAd, $this>
     */
    public function adSlots(): HasMany
    {
        return $this->hasMany(CategoryAd::class, 'songcategoryid', 'songcategoryid');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
