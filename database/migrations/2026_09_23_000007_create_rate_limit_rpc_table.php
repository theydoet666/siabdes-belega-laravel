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
        Schema::create('rate_limit_rpc', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 255)->unique('uq_kunci');
            $table->integer('jumlah')->default(1);
            $table->timestamp('jendela_mulai')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limit_rpc');
    }
};
