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
        Schema::table('tb_music', function (Blueprint $table) {
            $table->string('musicfileid')->nullable()->change();
            $table->string('upload_status', 20)->default('ready')->after('musicfileid');
            $table->text('upload_error')->nullable()->after('upload_status');
            $table->string('pending_file_path')->nullable()->after('upload_error');
            $table->timestamp('uploaded_at')->nullable()->after('pending_file_path');

            $table->index('upload_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_music', function (Blueprint $table) {
            $table->dropIndex(['upload_status']);
            $table->dropColumn([
                'upload_status',
                'upload_error',
                'pending_file_path',
                'uploaded_at',
            ]);
        });
    }
};
