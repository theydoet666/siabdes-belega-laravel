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
        Schema::table('pengaturan_sistem', function (Blueprint $table) {
            $table->string('sub_nama_desa', 255)->nullable()->after('nama_desa');
            $table->string('logo_lembaga_url', 500)->nullable()->after('logo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_sistem', function (Blueprint $table) {
            $table->dropColumn(['sub_nama_desa', 'logo_lembaga_url']);
        });
    }
};
