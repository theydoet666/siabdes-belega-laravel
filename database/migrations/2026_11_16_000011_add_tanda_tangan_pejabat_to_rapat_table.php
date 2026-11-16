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
            $table->string('verifikator_ttd', 500)->nullable()->after('verifikator_nip');
            $table->string('pelaksana_ttd', 500)->nullable()->after('pelaksana_nip');
            $table->string('penandatangan_ttd', 500)->nullable()->after('penandatangan_lokasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapat', function (Blueprint $table) {
            $table->dropColumn([
                'verifikator_ttd',
                'pelaksana_ttd',
                'penandatangan_ttd',
            ]);
        });
    }
};
