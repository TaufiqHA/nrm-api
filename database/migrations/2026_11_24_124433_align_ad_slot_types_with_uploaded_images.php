<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Selaraskan jenis iklan untuk slot yang sudah memiliki gambar terunggah.
     *
     * Berkas gambar yang diunggah lewat panel admin menang atas pilihan jenis
     * lain. Data lama sempat menyimpan gambar sekaligus tipe AdMob, sehingga
     * aplikasi tidak pernah memakai gambarnya. Migrasi ini hanya menyentuh slot
     * yang memang memiliki berkas gambar.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tb_application')) {
            return;
        }

        $adFields = [
            'applicationads1', 'applicationads2',
            'applicationadshome', 'applicationadshometop', 'applicationadshomebottom',
            'applicationadslyric', 'applicationadslyrictop', 'applicationadslyricbottom',
            'applicationadsmusiktop', 'applicationadsmusik', 'applicationadsmusikbottom',
            'applicationadsbottom',
        ];

        DB::table('tb_application')->get()->each(function ($setting) use ($adFields) {
            $updates = [];

            foreach ($adFields as $field) {
                $image = $setting->{$field} ?? null;
                $type = $setting->{$field.'type'} ?? null;

                if (filled($image) && $type !== 'image') {
                    $updates[$field.'type'] = 'image';
                    $updates[$field.'unit'] = null;
                }
            }

            if ($updates !== []) {
                DB::table('tb_application')
                    ->where('applicationid', $setting->applicationid)
                    ->update($updates);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada pembalikan: data lama yang ambigu tidak bisa direkonstruksi.
    }
};
