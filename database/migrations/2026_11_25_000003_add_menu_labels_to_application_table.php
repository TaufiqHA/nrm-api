<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan label kustom untuk menu yang dapat diedit inline.
     *
     * Label ini dipakai di panel admin dan aplikasi mobile, sehingga admin
     * dapat mengganti nama menu tanpa mengubah kode.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->string('applicationmusiklabel', 50)->default('Menu Musik');
            $table->string('applicationlyriclabel', 50)->default('Menu Lirik');
            $table->string('applicationmenuindonesialabel', 50)->default('Menu Indonesia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationmusiklabel',
                'applicationlyriclabel',
                'applicationmenuindonesialabel',
            ]);
        });
    }
};
