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
        Schema::create('pengaturan_sistem', function (Blueprint $table) {
            $table->integer('id')->default(1)->primary();
            $table->string('nama_sistem', 255)->default('SIABDES Belega');
            $table->string('subjudul_sistem', 255)->default('Sistem Absensi Rapat Kantor Desa Belega');
            $table->string('nama_desa', 255)->default('Pemerintah Desa Belega');
            $table->string('alamat_desa', 500)->default('Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581');
            $table->string('kecamatan', 255)->default('Kecamatan Blahbatuh');
            $table->string('kabupaten', 255)->default('Kabupaten Gianyar');
            $table->string('provinsi', 255)->default('Bali');
            $table->string('logo_url', 500)->nullable();
            $table->string('favicon_url', 500)->nullable();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->char('diperbarui_oleh', 36)->nullable();

            $table->foreign('diperbarui_oleh')->references('id')->on('profil')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
    }
};
