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
        Schema::create('workloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_sks_pendidikan', 5, 2)->default(0);
            $table->decimal('total_sks_penelitian', 5, 2)->default(0);
            $table->decimal('total_sks_pengabdian', 5, 2)->default(0);
            $table->decimal('total_sks_penunjang', 5, 2)->default(0);
            $table->enum('conclusion', ['memenuhi', 'tidak_memenuhi', 'belum_dihitung'])->default('belum_dihitung');
            $table->timestamps();
            $table->unique(['academic_period_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workloads');
    }
};
