<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan status visibilitas menu Musik dan Lirik.
     *
     * Nilai 'Y' berarti menu ditampilkan, 'N' berarti disembunyikan dari
     * panel admin maupun aplikasi mobile.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->string('applicationmusikactive', 1)->default('Y');
            $table->string('applicationlyricactive', 1)->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationmusikactive',
                'applicationlyricactive',
            ]);
        });
    }
};
