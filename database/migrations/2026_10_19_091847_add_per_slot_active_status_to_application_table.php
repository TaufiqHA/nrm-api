<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan status aktif per slot iklan utama. Kolom lama
     * `applicationadsactive` tetap dipertahankan sebagai status agregat
     * agar aplikasi yang belum memakai field per slot tetap kompatibel.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->string('applicationads1active', 1)->default('Y');
            $table->string('applicationads2active', 1)->default('Y');
        });

        DB::table('tb_application')->update([
            'applicationads1active' => DB::raw('applicationadsactive'),
            'applicationads2active' => DB::raw('applicationadsactive'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationads1active',
                'applicationads2active',
            ]);
        });
    }
};
