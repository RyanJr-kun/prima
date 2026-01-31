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
        Schema::create('aproval_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prodi_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('campus', ['kampus_1', 'kampus_2'])->nullable();
            $table->enum('shift', ['pagi', 'malam'])->nullable();
            $table->enum('type', [
                'distribusi_matkul',
                'jadwal_perkuliahan',
                'beban_kerja_dosen',
                'kalender_akademik'
            ]);
            $table->enum('status', [
                'draft',
                'submitted',
                'approved_kaprodi',
                'approved_wadir1',
                'approved_wadir2',
                'approved_direktur',
                'rejected'
            ])->default('draft');
            $table->text('feedback_message')->nullable();
            $table->foreignId('action_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['academic_period_id', 'prodi_id', 'campus', 'shift', 'type'], 'unique_doc_per_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aproval_documents');
    }
};
