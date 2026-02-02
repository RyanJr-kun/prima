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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_distribution_id')
                ->constrained('course_distributions')
                ->cascadeOnDelete();
            $table->foreignId('course_id')->cascadeOnDelete();
            $table->foreignId('study_class_id')->cascadeOnDelete();
            $table->foreignId('room_id')->nullOnDelete();
            $table->foreignId('user_id')->nullable();
            $table->enum('day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
            $table->json('time_slot_ids');
            $table->enum('component', ['teori', 'praktik', 'lapangan'])->default('teori');

            $table->timestamps();
            $table->index(['day', 'room_id']);
            $table->index(['day', 'user_id']);
            $table->index(['day', 'study_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
