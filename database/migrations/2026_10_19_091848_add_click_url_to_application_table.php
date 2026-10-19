<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * URL tujuan saat banner gambar diklik, satu kolom per slot iklan.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->text('applicationads1clickurl')->nullable();
            $table->text('applicationads2clickurl')->nullable();
            $table->text('applicationadsbottomclickurl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationads1clickurl',
                'applicationads2clickurl',
                'applicationadsbottomclickurl',
            ]);
        });
    }
};
