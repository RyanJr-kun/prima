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
        Schema::create('course_distributions', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_class_id')->constrained('study_classes')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();   
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('pddikti_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('referensi')->nullable(); 
            $table->text('luaran')->nullable();   
            $table->timestamps();
            $table->unique(
                ['academic_period_id', 'study_class_id', 'course_id'], 
                'unique_dist_per_class'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_distributions');
    }
};
