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
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $classes = StudyClass::with('prodi')
            ->orderBy('prodi_id')
            ->orderBy('name')
            ->get();

        $groupedSchedules = collect();
        $classId = $request->input('class_id');

        if (!$activePeriod) {
            return view('content.landingpage.jadwal', [
                'groupedSchedules' => $groupedSchedules,
                'activePeriod' => null,
                'classes' => [],
                'classId' => null
            ]);
        }

        if ($classId) {
            $query = Schedule::with(['course', 'studyClass.prodi', 'room', 'lecturer'])
                ->whereHas('courseDistribution', function ($q) use ($activePeriod) {
                    $q->where('academic_period_id', $activePeriod->id);
                })
                ->where('study_class_id', $classId);

            $schedules = $query
                ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
                ->get()
                ->sortBy(function ($schedule) {
                    $rawSlots = $schedule->time_slot_ids;
                    $slots = is_array($rawSlots) ? $rawSlots : json_decode($rawSlots, true);

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
