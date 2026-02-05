<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Prodi;
use App\Models\Schedule;
use App\Models\TimeSlots;
use App\Models\StudyClass; // Pastikan Model ini ada
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function index()
    {
        return view('content.landingpage.home');
    }

    public function jadwal(Request $request)
    {
        // 1. Data Dasar
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        // Ambil semua kelas untuk Dropdown Filter (Urutkan biar rapi)
        // Kita load relasi prodi agar di dropdown terlihat jelas (misal: TI - 1A)
        $classes = StudyClass::with('prodi')
            ->orderBy('prodi_id')
            ->orderBy('name')
            ->get();

        // Variabel untuk menampung hasil
        $groupedSchedules = collect();
        $classId = $request->input('class_id');

        // Jika Periode belum aktif, return view kosong
        if (!$activePeriod) {
            return view('content.landingpage.jadwal', [
                'groupedSchedules' => $groupedSchedules,
                'activePeriod' => null,
                'classes' => [],
                'classId' => null
            ]);
        }

        // 2. Logika Pencarian (Hanya jalan jika ada class_id dipilih)
        if ($classId) {
            $query = Schedule::with(['course', 'studyClass.prodi', 'room', 'lecturer'])
                ->whereHas('courseDistribution', function ($q) use ($activePeriod) {
                    $q->where('academic_period_id', $activePeriod->id);
                })
                ->where('study_class_id', $classId); // Filter berdasarkan Kelas yang dipilih

            // Urutkan berdasarkan Hari (Senin -> Sabtu) dan Jam Mulai
            $schedules = $query
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
                ->get()
                // --- PERBAIKAN DI SINI ---
                ->sortBy(function ($schedule) {
                    // Cek tipe data: Jika sudah array, pakai langsung. Jika belum, decode dulu.
                    $rawSlots = $schedule->time_slot_ids;
                    $slots = is_array($rawSlots) ? $rawSlots : json_decode($rawSlots, true);

                    // Pastikan slots tidak null/kosong sebelum ambil min()
                    return !empty($slots) ? min($slots) : 9999;
                });

            $groupedSchedules = $schedules->groupBy('day');
        }

        return view('content.landingpage.jadwal', compact(
            'groupedSchedules',
            'activePeriod',
            'classes',
            'classId'
        ));
    }
}
