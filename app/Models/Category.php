<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'sort_order',
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
     * Get the songs for the category.
     *
     * @return HasMany<Song, $this>
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'songcategory', 'songcategoryid');
    }
}
