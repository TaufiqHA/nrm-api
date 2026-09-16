<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $table = 'tb_application';

    protected $primaryKey = 'applicationid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'applicationcompany',
        'applicationname',
        'applicationads1',
        'applicationads2',
        'applicationadsactive',
        'applicationadsbottom',
        'applicationadsbottomactive',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applicationid' => 'integer',
        ];
    }
}
