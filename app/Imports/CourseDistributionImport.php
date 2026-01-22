<?php

namespace App\Imports;

use App\Models\CourseDistribution;
use App\Models\Course;
use App\Models\User;
use App\Models\StudyClass; 
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CourseDistributionImport implements ToCollection, WithStartRow
{
    protected $classId;
    protected $periodId;

    // CONSTRUCTOR: Menerima ID Kelas yang dipilih dari Controller
    public function __construct($classId)
    {
        $this->classId = $classId;
        
        // Otomatis cari Tahun Ajaran Aktif dari kelas tersebut
        $kelas = StudyClass::find($classId);
        $this->periodId = $kelas ? $kelas->academic_period_id : null;
    }

    // MULAI BACA DARI BARIS KE-2 (Skip Header)
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        if (!$this->periodId) return;

        foreach ($rows as $row) {
            // --- 1. MAPPING KOLOM (Sesuaikan dengan urutan Header baru) ---
            $excelKode      = trim($row[1]); // Kolom Kode
            $excelNama      = trim($row[2]); // Kolom Nama Matkul
            $excelDosen     = trim($row[3]); // Kolom Dosen Utama
            $excelPddikti   = trim($row[4]); // Kolom Dosen PDDIKTI (Baru)
            
            // Referensi & Luaran geser indexnya
            $excelRef       = $row[5] ?? null; 
            $excelLuaran    = $row[6] ?? null;

            if (empty($excelKode)) continue;

            // --- 2. CARI MATKUL (Logic Double Check tadi) ---
            $course = Course::where('code', $excelKode)->first();
            if (!$course && !empty($excelNama)) {
                $course = Course::where('name', $excelNama)->first();
            }
            if (!$course) continue; // Matkul ga ketemu, skip.

            // --- 3. CARI DOSEN UTAMA ---
            $dosenUtama = null;
            if (!empty($excelDosen)) {
                $dosenUtama = User::where('name', 'LIKE', '%' . $excelDosen . '%')->first();
            }

            // --- 4. CARI DOSEN PDDIKTI (Logic Baru) ---
            $dosenPddikti = null;
            if (!empty($excelPddikti)) {
                // Cari user yang namanya mirip inputan excel
                $dosenPddikti = User::where('name', 'LIKE', '%' . $excelPddikti . '%')->first();
            }

            // --- 5. EKSEKUSI SIMPAN ---
            CourseDistribution::updateOrCreate(
                [
                    'academic_period_id' => $this->periodId,
                    'study_class_id'     => $this->classId,
                    'course_id'          => $course->id,
                ],
                [
                    'user_id'          => $dosenUtama ? $dosenUtama->id : null,
                    'pddikti_user_id'  => $dosenPddikti ? $dosenPddikti->id : null, // <--- UPDATE DISINI
                    'referensi'        => $excelRef,
                    'luaran'           => $excelLuaran,
                ]
            );
        }
    }
}