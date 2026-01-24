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
        Schema::create('study_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('shift', ['pagi', 'malam'])->default('pagi');
            $table->foreignId('prodi_id')->constrained('prodis');
            $table->integer('semester');
            $table->string('angkatan');
            $table->integer('total_students')->default(0);
            $table->foreignId('kurikulum_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_advisor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_classes');
    }
};
