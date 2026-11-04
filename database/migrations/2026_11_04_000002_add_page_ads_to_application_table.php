<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan slot iklan khusus halaman utama dan halaman lirik only.
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->text('applicationadshome')->nullable();
            $table->string('applicationadshometype', 20)->default('image');
            $table->text('applicationadshomeunit')->nullable();
            $table->string('applicationadshomeactive', 1)->default('Y');
            $table->text('applicationadshomeclickurl')->nullable();

            $table->text('applicationadslyric')->nullable();
            $table->string('applicationadslyrictype', 20)->default('image');
            $table->text('applicationadslyricunit')->nullable();
            $table->string('applicationadslyricactive', 1)->default('Y');
            $table->text('applicationadslyricclickurl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationadshome',
                'applicationadshometype',
                'applicationadshomeunit',
                'applicationadshomeactive',
                'applicationadshomeclickurl',
                'applicationadslyric',
                'applicationadslyrictype',
                'applicationadslyricunit',
                'applicationadslyricactive',
                'applicationadslyricclickurl',
            ]);
        });
    }
};
