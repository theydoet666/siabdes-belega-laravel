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
        Schema::create('rapat', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('kode', 20)->unique();
            $table->string('judul', 255);
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('tempat', 255);
            $table->string('penyelenggara', 255)->default('Pemerintah Desa Belega');
            $table->text('catatan')->nullable();
            $table->enum('status', ['draft', 'dibuka', 'ditutup'])->default('draft');
            $table->string('pin_kiosk', 255)->nullable();
            $table->integer('retensi_hari')->default(90);
            $table->timestamp('foto_dihapus_pada')->nullable();
            $table->string('penandatangan_nama', 255)->default('I WAYAN SUDARSANA, S.Sos.');
            $table->string('penandatangan_jabatan', 255)->default('Perbekel Belega');
            $table->string('penandatangan_nip', 50)->default('');
            $table->string('penandatangan_lokasi', 255)->default('Belega');
            $table->char('dibuat_oleh', 36);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('dibuat_oleh')->references('id')->on('profil')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapat');
    }
};
