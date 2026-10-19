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
        'applicationads1type',
        'applicationads1unit',
        'applicationads1active',
        'applicationads1clickurl',
        'applicationads2',
        'applicationads2type',
        'applicationads2unit',
        'applicationads2active',
        'applicationads2clickurl',
        'applicationadsactive',
        'applicationadsbottom',
        'applicationadsbottomtype',
        'applicationadsbottomunit',
        'applicationadsbottomactive',
        'applicationadsbottomclickurl',
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
