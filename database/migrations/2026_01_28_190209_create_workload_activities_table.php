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

            // Kategori: Pendidikan (Mengajar), Penelitian, Pengabdian, Penunjang
            $table->enum('category', ['pendidikan', 'penelitian', 'pengabdian', 'penunjang']);

            // Data Utama Kegiatan
            $table->string('activity_name'); // Nama Matkul + Kelas (otomatis digabung)
            $table->decimal('sks_load', 5, 2)->default(0); // SKS (Otomatis dari distribusi)

            // --- KOLOM KHUSUS (YANG AKAN DI-EDIT MANUAL) ---
            $table->integer('realisasi_pertemuan')->nullable()->default(14); // Default 14, tapi bisa diedit jadi 7, 10, dll
            $table->string('jenis_ujian')->nullable()->default('UTS, UAS'); // Default lengkap, bisa diedit jadi 'UAS'
            // -----------------------------------------------

            $table->string('description')->nullable(); // Keterangan tambahan
            $table->string('document_path')->nullable(); // Bukti Ajar (Jurnal/Presensi)

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
