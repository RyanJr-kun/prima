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

            $table->string('distribution_status')->default('draft'); // draft, pending, approved
            $table->foreignId('distribution_kaprodi_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('distribution_kaprodi_at')->nullable();
            $table->foreignId('distribution_wadir_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('distribution_wadir_at')->nullable();
            $table->foreignId('distribution_direktur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('distribution_direktur_at')->nullable();

            $table->string('bkd_status')->default('draft');
            $table->foreignId('bkd_kaprodi_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('bkd_kaprodi_at')->nullable();
            $table->foreignId('bkd_wadir_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('bkd_wadir_at')->nullable();
            $table->foreignId('bkd_direktur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('bkd_direktur_at')->nullable();

            $table->string('calendar_status')->default('draft');
            $table->boolean('schedule_is_published')->default(false);
            $table->foreignId('calendar_wadir_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('calendar_wadir_at')->nullable();
            $table->foreignId('calendar_direktur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('calendar_direktur_at')->nullable();
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
