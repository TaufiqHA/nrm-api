<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan slot iklan atas dan bawah khusus halaman lirik only.
     * Slot ini terpisah dari slot halaman musik (slot 1 dan banner bawah).
     */
    public function up(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->text('applicationadslyrictop')->nullable();
            $table->string('applicationadslyrictoptype', 20)->default('image');
            $table->text('applicationadslyrictopunit')->nullable();
            $table->string('applicationadslyrictopactive', 1)->default('Y');
            $table->text('applicationadslyrictopclickurl')->nullable();

            $table->text('applicationadslyricbottom')->nullable();
            $table->string('applicationadslyricbottomtype', 20)->default('image');
            $table->text('applicationadslyricbottomunit')->nullable();
            $table->string('applicationadslyricbottomactive', 1)->default('Y');
            $table->text('applicationadslyricbottomclickurl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_application', function (Blueprint $table) {
            $table->dropColumn([
                'applicationadslyrictop',
                'applicationadslyrictoptype',
                'applicationadslyrictopunit',
                'applicationadslyrictopactive',
                'applicationadslyrictopclickurl',
                'applicationadslyricbottom',
                'applicationadslyricbottomtype',
                'applicationadslyricbottomunit',
                'applicationadslyricbottomactive',
                'applicationadslyricbottomclickurl',
            ]);
        });
    }
};
