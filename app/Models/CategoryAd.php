<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CategoryAd extends Model
{
    /**
     * Slot iklan yang tersedia untuk setiap kategori.
     */
    public const SLOTS = ['top', 'popup', 'bottom'];

    protected $table = 'category_ads';

    protected $primaryKey = 'categoryadid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'songcategoryid',
        'slot',
        'image',
        'type',
        'unit',
        'active',
        'clickurl',
    ];

    /**
     * Get the category that owns the ad slot.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'songcategoryid', 'songcategoryid');
    }

    /**
     * Hapus berkas gambar slot dari disk publik bila masih berupa path lokal.
     */
    public function deleteStoredImage(): void
    {
        if (empty($this->image) || filter_var($this->image, FILTER_VALIDATE_URL)) {
            return;
        }

        if (Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
    }
}
