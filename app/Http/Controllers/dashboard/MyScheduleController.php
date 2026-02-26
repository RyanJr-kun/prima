<?php

namespace App\Http\Controllers\dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\AcademicPeriod;
use App\Models\Prodi;
use App\Models\StudyClass;
use Illuminate\Support\Facades\Auth;

class MyScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $prodis = Prodi::orderBy('name')->get();

        if (!$activePeriod) {
            return view('content.dashboard.jadwal_saya', [
                'groupedSchedules' => [],
                'activePeriod' => null,
                'prodis' => []
            ]);
        }

        $query = Schedule::with(['course', 'studyClass.prodi', 'room', 'lecturer'])
            ->where('user_id', $user->id)
            ->whereHas('courseDistribution', function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id);
            });

        // filter pencarian 
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->whereHas('course', fn($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('studyClass', function ($sc) use ($search) {
                        $sc->where('name', 'like', "%{$search}%")
                            ->orWhere('angkatan', 'like', "%{$search}%")
                            ->orWhereHas('prodi', function ($p) use ($search) {
                                $p->where('code', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Filter Prodi
        if ($request->filled('prodi_id')) {
            $query->whereHas('studyClass', fn($q) => $q->where('prodi_id', $request->prodi_id));
        }

        //filter hari
        $schedules = $query
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
            ->get();

        $groupedSchedules = $schedules->groupBy('day');

        return view('content.dashboard.jadwal_saya', compact('groupedSchedules', 'activePeriod', 'prodis'));
    }

    public function storePic(Request $request)
    {
        $request->validate([
            'study_class_id' => 'required|exists:study_classes,id',
            'pic_name'       => 'required|string|max:100',
            'pic_contact'    => 'required|string|max:20',
        ]);

        $studyClass = StudyClass::findOrFail($request->study_class_id);

        // Update Data PIC di Kelas
        $studyClass->update([
            'pic_name' => $request->pic_name,
            'pic_contact' => $request->pic_contact
        ]);

        return back()->with('success', 'PIC Mahasiswa berhasil disimpan!');
    }
}
