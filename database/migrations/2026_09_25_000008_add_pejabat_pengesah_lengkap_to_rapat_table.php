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
        Schema::table('rapat', function (Blueprint $table) {
            // Pihak 1: Verifikator (Sekretaris Desa)
            $table->boolean('verifikator_tampil')->default(true)->after('foto_dihapus_pada');
            $table->string('verifikator_label', 255)->default('Telah dilakukan verifikasi')->after('verifikator_tampil');
            $table->string('verifikator_jabatan', 255)->default('Sekretaris Desa')->after('verifikator_label');
            $table->string('verifikator_nama', 255)->nullable()->after('verifikator_jabatan');
            $table->string('verifikator_nip', 50)->nullable()->default('')->after('verifikator_nama');

            // Pihak 2: Pelaksana Kegiatan (Kasi / Kaur)
            $table->boolean('pelaksana_tampil')->default(true)->after('verifikator_nip');
            $table->string('pelaksana_jabatan', 255)->default('Kasi Pelayanan')->after('pelaksana_tampil');
            $table->string('pelaksana_nama', 255)->nullable()->after('pelaksana_jabatan');
            $table->string('pelaksana_nip', 50)->nullable()->default('')->after('pelaksana_nama');

            // Pihak 3: Mengetahui / Mengesahkan (Perbekel / Plt. Perbekel)
            $table->boolean('mengetahui_tampil')->default(true)->after('pelaksana_nip');
            $table->string('mengetahui_label', 255)->default('Mengetahui :')->after('mengetahui_tampil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapat', function (Blueprint $table) {
            $table->dropColumn([
                'verifikator_tampil',
                'verifikator_label',
                'verifikator_jabatan',
                'verifikator_nama',
                'verifikator_nip',
                'pelaksana_tampil',
                'pelaksana_jabatan',
                'pelaksana_nama',
                'pelaksana_nip',
                'mengetahui_tampil',
                'mengetahui_label',
            ]);
        });
    }
};
