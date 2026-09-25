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
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->char('aktor', 36)->nullable();
            $table->string('aksi', 255);
            $table->string('tabel_target', 100);
            $table->string('baris_id', 100)->nullable();
            $table->json('rincian')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('aktor')->references('id')->on('profil')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
