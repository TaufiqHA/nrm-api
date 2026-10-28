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
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->index()->after('songcategoryname');
        });

        // Backfill urutan awal berdasarkan ID agar kategori lama tetap terurut.
        $categories = DB::table('categories')->orderBy('songcategoryid')->pluck('songcategoryid');
        foreach ($categories as $index => $id) {
            DB::table('categories')
                ->where('songcategoryid', $id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
