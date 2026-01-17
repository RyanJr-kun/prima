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
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->string('distribution_status')->default('draft');
            $table->foreignId('distribution_approved_by')->nullable()->constrained('users');
            $table->timestamp('distribution_approved_at')->nullable();
            $table->boolean('schedule_is_published')->default(false);
            $table->string('calendar_status')->default('draft');
            $table->timestamp('calendar_approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
