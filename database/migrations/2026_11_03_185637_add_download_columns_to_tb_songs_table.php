<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_songs', function (Blueprint $table) {
            $table->text('songvideourl')->nullable()->after('songurl');
            $table->string('download_status', 20)->nullable()->after('songvideourl');
            $table->unsignedTinyInteger('download_progress')->default(0)->after('download_status');
            $table->text('download_error')->nullable()->after('download_progress');
            $table->timestamp('downloaded_at')->nullable()->after('download_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_songs', function (Blueprint $table) {
            $table->dropColumn([
                'songvideourl',
                'download_status',
                'download_progress',
                'download_error',
                'downloaded_at',
            ]);
        });
    }
};
