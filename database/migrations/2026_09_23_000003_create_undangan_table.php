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
        Schema::create('undangan', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('rapat_id', 36);
            $table->string('nama', 255);
            $table->string('jabatan', 255)->default('');
            $table->string('instansi', 255)->default('');
            $table->string('hp', 30)->default('');
            $table->enum('sumber', ['import', 'tambahan'])->default('import');
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->string('nama_cari', 255)->nullable();
            } else {
                $table->string('nama_cari', 255)->storedAs("LOWER(REGEXP_REPLACE(`nama`, '[^a-zA-Z0-9 ]', ' '))");
            }
            $table->integer('urutan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('rapat_id')->references('id')->on('rapat')->cascadeOnDelete();
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText('nama_cari', 'ft_nama_cari');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('undangan');
    }
};
