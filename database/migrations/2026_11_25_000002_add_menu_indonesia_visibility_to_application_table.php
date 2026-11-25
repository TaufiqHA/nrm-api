<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan status visibilitas menu Indonesia.
     *
     * Nilai 'Y' berarti menu ditampilkan, 'N' berarti disembunyikan dari
     * panel admin maupun aplikasi mobile. Kolom ini terpisah dari
     * `Category.is_active` agar kategori dan menu dapat dikelola sendiri-sendiri.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->string('applicationmenuindonesiaactive', 1)->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn('applicationmenuindonesiaactive');
        });
    }
};
