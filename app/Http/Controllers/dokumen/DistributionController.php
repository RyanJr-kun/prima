<?php

namespace App\Http\Controllers\dokumen;
use App\Http\Controllers\Controller;
use App\Models\CourseDistribution;
use App\Models\StudyClass;
use App\Models\User;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;


class DistributionController extends Controller
{
    public function index()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Belum ada Periode Akademik yang Aktif!');
        }
        $distributions = CourseDistribution::with(['studyClass', 'course', 'user'])
                        ->where('academic_period_id', $activePeriod->id)
                        ->get()
                        ->groupBy('study_class_id');

        return view('content.distribution.index', compact('distributions', 'activePeriod'));
    }

    // 2. FORM INPUT
    public function create()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();
        $classes = StudyClass::where('academic_period_id', $activePeriod->id)->get();
        $dosens = User::role('dosen')->get();

        return view('content.distribution.create', compact('classes', 'dosens'));
    }

    // 3. SIMPAN DATA
    public function store(Request $request)
    {
        $request->validate([
            'study_class_id' => 'required',
            'course_id' => 'required',
            'user_id' => 'required', 
        ]);

        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        $exists = CourseDistribution::where([
            'academic_period_id' => $activePeriod->id,
            'study_class_id' => $request->study_class_id,
            'course_id' => $request->course_id,
        ])->exists();

        if ($exists) {
            return back()->with('error', 'Mata kuliah ini sudah didistribusikan di kelas tersebut!');
        }

        CourseDistribution::create([
            'academic_period_id' => $activePeriod->id,
            'study_class_id' => $request->study_class_id,
            'course_id' => $request->course_id,
            'user_id' => $request->user_id,
            'pddikti_user_id' => $request->pddikti_user_id, 
            'referensi' => $request->referensi,
            'luaran' => $request->luaran,
        ]);

        return redirect()->route('distributions.index')->with('success', 'Data tersimpan!');
    }

    public function getCoursesByClass($classId)
    {
        $kelas = StudyClass::with('kurikulum')->find($classId);
        $courses = \App\Models\Course::where('kurikulum_id', $kelas->kurikulum_id)
                    ->orderBy('semester', 'asc')
                    ->orderBy('name', 'asc')
                    ->get();

        return response()->json($courses);
    }
    public function show(Request $request)
    {
        // 
    }
    public function edit(Request $request)
    {
        //
    }
    public function update(Request $request)
    {
        //
    }
    public function destroy($id)
    {
        try {
            $distribution = CourseDistribution::findOrFail($id);

            $distribution->delete();
            return redirect()->route('distributions.index')
                ->with('success', 'Distribusi mata kuliah berhasil dihapus!');

        } catch (QueryException $e) {
        
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('distributions.index')
                    ->with('error', 'Gagal menghapus: Data distribusi mata kuliah ini sedang digunakan.');
            }

            return redirect()->route('distributions.index')->with('error', 'Terjadi kesalahan sistem saat menghapus data distribusi mata kuliah.');
        }
        
    }
}
