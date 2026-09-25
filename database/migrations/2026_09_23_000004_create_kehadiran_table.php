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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('rapat_id', 36);
            $table->char('undangan_id', 36);
            $table->string('ttd_path', 500);
            $table->string('foto_path', 500)->nullable();
            $table->enum('jalur', ['kiosk', 'mandiri', 'operator'])->default('kiosk');
            $table->string('perangkat_id', 255)->nullable();
            $table->timestamp('waktu_perangkat')->nullable();
            $table->string('diwakili_oleh', 255)->nullable();
            $table->boolean('dibatalkan')->default(false);
            $table->text('alasan_batal')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('rapat_id')->references('id')->on('rapat')->cascadeOnDelete();
            $table->foreign('undangan_id')->references('id')->on('undangan')->cascadeOnDelete();
            $table->unique('id', 'uq_idempoten');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
