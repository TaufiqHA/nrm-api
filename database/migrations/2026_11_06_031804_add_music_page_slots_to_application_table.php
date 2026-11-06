<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan slot iklan khusus halaman BUKU MERAH (Musik): atas, pop-up,
     * dan bawah. Slot ini terpisah dari slot utama halaman karaoke dan slot
     * halaman lirik.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->text('applicationadsmusiktop')->nullable();
            $table->string('applicationadsmusiktoptype', 20)->default('image');
            $table->text('applicationadsmusiktopunit')->nullable();
            $table->string('applicationadsmusiktopactive', 1)->default('Y');
            $table->text('applicationadsmusiktopclickurl')->nullable();

            $table->text('applicationadsmusik')->nullable();
            $table->string('applicationadsmusiktype', 20)->default('image');
            $table->text('applicationadsmusikunit')->nullable();
            $table->string('applicationadsmusikactive', 1)->default('Y');
            $table->text('applicationadsmusikclickurl')->nullable();

            $table->text('applicationadsmusikbottom')->nullable();
            $table->string('applicationadsmusikbottomtype', 20)->default('image');
            $table->text('applicationadsmusikbottomunit')->nullable();
            $table->string('applicationadsmusikbottomactive', 1)->default('Y');
            $table->text('applicationadsmusikbottomclickurl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationadsmusiktop',
                'applicationadsmusiktoptype',
                'applicationadsmusiktopunit',
                'applicationadsmusiktopactive',
                'applicationadsmusiktopclickurl',
                'applicationadsmusik',
                'applicationadsmusiktype',
                'applicationadsmusikunit',
                'applicationadsmusikactive',
                'applicationadsmusikclickurl',
                'applicationadsmusikbottom',
                'applicationadsmusikbottomtype',
                'applicationadsmusikbottomunit',
                'applicationadsmusikbottomactive',
                'applicationadsmusikbottomclickurl',
            ]);
        });
    }
};
