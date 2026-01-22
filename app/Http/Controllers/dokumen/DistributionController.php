<?php

namespace App\Http\Controllers\dokumen;
use App\Http\Controllers\Controller;
use App\Models\CourseDistribution;
use App\Models\StudyClass;
use App\Models\User;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Exports\CourseDistributionTemplateExport;
use App\Imports\CourseDistributionImport;
use App\Models\Prodi;
use Maatwebsite\Excel\Facades\Excel;


class DistributionController extends Controller
{
        public function index(Request $request)
    {
        // 1. Ambil semua periode untuk dropdown filter
        $periods = AcademicPeriod::orderBy('name', 'desc')->get();

        // 2. Tentukan periode aktif (dari request atau default is_active)
        if ($request->has('period_id')) {
            $activePeriod = $periods->where('id', $request->period_id)->first();
        } else {
            $activePeriod = $periods->where('is_active', true)->first();
        }

        // Fallback jika tidak ada periode aktif sama sekali
        if (!$activePeriod) {
            $activePeriod = $periods->first();
        }

        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Belum ada Periode Akademik yang tersedia!');
        }

        $prodis = Prodi::all();

        // 3. Query Distribusi dengan Filter
        $query = CourseDistribution::with(['studyClass.prodi', 'course', 'user'])
                        ->where('academic_period_id', $activePeriod->id);

        // Filter Prodi (via relasi studyClass)
        if ($request->filled('prodi_id')) {
            $query->whereHas('studyClass', function($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        // Filter Semester (via relasi studyClass)
        if ($request->filled('semester')) {
            $query->whereHas('studyClass', function($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        // Eksekusi query dan grouping
        $distributions = $query->get()->groupBy('study_class_id');

        // 4. Data untuk Modal Import (Study Classes pada periode terpilih)
        $study_classes = StudyClass::with('prodi')
                        ->where('academic_period_id', $activePeriod->id)
                        ->get();

        return view('content.distribution.index', compact('distributions', 'activePeriod', 'periods', 'study_classes', 'prodis'));
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
        if (!$activePeriod) {
            return back()->with('error', 'Gagal Simpan: Tidak ada Tahun Ajaran yang aktif saat ini!');
        }

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
    public function edit($id)
    {
        $distribution = CourseDistribution::findOrFail($id);
        return response()->json($distribution);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'nullable', // Dosen boleh kosong (jika belum ada pengganti)
            'pddikti_user_id' => 'nullable',
            'referensi' => 'nullable|string',
            'luaran' => 'nullable|string',
        ]);

        $distribution = CourseDistribution::findOrFail($id);
        
        $distribution->update([
            'user_id' => $request->user_id,
            'pddikti_user_id' => $request->pddikti_user_id,
            'referensi' => $request->referensi,
            'luaran' => $request->luaran,
        ]);

        return back()->with('success', 'Data berhasil diperbarui!');
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

    public function downloadTemplate()
    {
        return Excel::download(new CourseDistributionTemplateExport, 'template_distribusi.xlsx');
    }

    // Method Proses Import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'study_class_id' => 'required|exists:study_classes,id',
        ]);

        set_time_limit(300);

        try {
            Excel::import(new CourseDistributionImport($request->study_class_id), $request->file('file'));
            return back()->with('success', 'Import Berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
