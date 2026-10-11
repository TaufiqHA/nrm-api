<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan konfigurasi jenis iklan dan Ad Unit ID AdMob per slot iklan
     * yang sudah ada (slot 1, slot 2, dan banner bawah).
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->string('applicationads1type', 20)->default('image');
            $table->text('applicationads1unit')->nullable();

            $table->string('applicationads2type', 20)->default('image');
            $table->text('applicationads2unit')->nullable();

            $table->string('applicationadsbottomtype', 20)->default('image');
            $table->text('applicationadsbottomunit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationads1type',
                'applicationads1unit',
                'applicationads2type',
                'applicationads2unit',
                'applicationadsbottomtype',
                'applicationadsbottomunit',
            ]);
        });
    }
};
