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
        Schema::create('workload_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workload_id')->constrained('workloads')->cascadeOnDelete();
            $table->enum('category', ['pendidikan', 'penelitian', 'pengabdian', 'penunjang']);
            $table->string('activity_name');
            $table->decimal('sks_load', 5, 2)->default(0);
            $table->decimal('sks_real', 5, 2)->default(0);
            $table->decimal('sks_assigned', 5, 2)->default(0);
            $table->integer('realisasi_pertemuan')->nullable()->default(14);
            $table->boolean('is_uts_maker')->default(false);
            $table->boolean('is_uas_maker')->default(false);
            $table->string('description')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workload_activities');
    }
};
