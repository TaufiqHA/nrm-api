<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_songs', function (Blueprint $table) {
            $table->text('songyoutubeurl')->nullable()->after('songurl');
        });

        DB::table('tb_songs')
            ->where(function ($query) {
                $query->where('songurl', 'like', '%youtube.com%')
                    ->orWhere('songurl', 'like', '%youtu.be%');
            })
            ->update(['songyoutubeurl' => DB::raw('songurl')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_songs', function (Blueprint $table) {
            $table->dropColumn('songyoutubeurl');
        });
    }
};
