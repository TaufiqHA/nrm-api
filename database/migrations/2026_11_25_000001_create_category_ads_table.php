<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan slot iklan per kategori lagu.
     *
     * Setiap kategori memiliki tiga slot (atas, pop-up, dan bawah) yang
     * dibuat otomatis oleh model Category saat kategori baru ditambahkan.
     * Migrasi ini juga mengisi slot untuk kategori yang sudah ada.
     */
    public function up(): void
    {
        Schema::create('category_ads', function (Blueprint $table) {
            $table->increments('categoryadid');
            $table->unsignedInteger('songcategoryid');
            $table->string('slot', 20);
            $table->text('image')->nullable();
            $table->string('type', 20)->default('image');
            $table->text('unit')->nullable();
            $table->string('active', 1)->default('Y');
            $table->text('clickurl')->nullable();
            $table->timestamps();

            $table->unique(['songcategoryid', 'slot']);
            $table->foreign('songcategoryid')
                ->references('songcategoryid')
                ->on('categories')
                ->cascadeOnDelete();
        });

        $slots = ['top', 'popup', 'bottom'];
        $now = now();

        DB::table('categories')
            ->select('songcategoryid')
            ->orderBy('songcategoryid')
            ->each(function ($category) use ($slots, $now) {
                foreach ($slots as $slot) {
                    DB::table('category_ads')->insert([
                        'songcategoryid' => $category->songcategoryid,
                        'slot' => $slot,
                        'type' => 'image',
                        'active' => 'Y',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_ads');
    }
};
