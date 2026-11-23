<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan slot banner iklan khusus halaman utama (layar landing):
     * banner atas dan banner bawah. Slot pop-up halaman utama
     * (`applicationadshome`) tetap dipertahankan.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->text('applicationadshometop')->nullable();
            $table->string('applicationadshometoptype', 20)->default('image');
            $table->text('applicationadshometopunit')->nullable();
            $table->string('applicationadshometopactive', 1)->default('Y');
            $table->text('applicationadshometopclickurl')->nullable();

            $table->text('applicationadshomebottom')->nullable();
            $table->string('applicationadshomebottomtype', 20)->default('image');
            $table->text('applicationadshomebottomunit')->nullable();
            $table->string('applicationadshomebottomactive', 1)->default('Y');
            $table->text('applicationadshomebottomclickurl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationadshometop',
                'applicationadshometoptype',
                'applicationadshometopunit',
                'applicationadshometopactive',
                'applicationadshometopclickurl',
                'applicationadshomebottom',
                'applicationadshomebottomtype',
                'applicationadshomebottomunit',
                'applicationadshomebottomactive',
                'applicationadshomebottomclickurl',
            ]);
        });
    }
};
