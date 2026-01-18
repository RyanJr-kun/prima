<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\StudyClass;
use App\Models\AcademicPeriod;
use App\Models\Kurikulum;
use App\Models\User;
use Illuminate\Http\Request;

class StudyClassController extends Controller
{
    public function index()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $prodis = \App\Models\Prodi::all();
        $dosens = User::role('dosen')->get();
        $kurikulums = Kurikulum::where('is_active', true)->get();
        $classes = StudyClass::with(['academicAdvisor', 'kurikulum'])
                    ->where('academic_period_id', $activePeriod->id ?? 0)
                    ->get();

        return view('content.master.classes.index', compact('classes','prodis','dosens', 'kurikulums'));
    }

    public function create()
    {

        return view('content.master.classes.create', );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'semester' => 'required|numeric',
            'prodi_id' => 'required|exists:prodis,id',
            'angkatan' => 'required|numeric',
            'total_students' => 'required|numeric',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'academic_advisor_id' => 'required|exists:users,id',
        ]);

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        StudyClass::create([
            'academic_period_id' => $activePeriod->id,
            'name' => $request->name,
            'prodi_id' => $request->prodi_id,
            'semester' => $request->semester,
            'angkatan' => $request->angkatan,
            'total_students' => $request->total_students,
            'kurikulum_id' => $request->kurikulum_id,
            'academic_advisor_id' => $request->academic_advisor_id,
        ]);

        return redirect()->route('master.kelas.index')->with('success', 'Kelas berhasil dibuat!');
    }

    public function edit(Request $request)
    {
        //
    }

    public function update(Request $request)
    {
        //
    }

    public function destroy(Request $request)
    {
        //
    }
}
