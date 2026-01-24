<?php

namespace App\Http\Controllers\dokumen;

use App\Models\User;
use App\Exports\DistributionExport;
use App\Imports\DistributionUpdateImport;
use App\Models\Prodi;
use App\Models\Course;
use App\Models\StudyClass;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\CourseDistribution;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use App\Imports\CourseDistributionImport;
use App\Exports\CourseDistributionTemplateExport;


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
            $query->whereHas('studyClass', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        // Filter Semester (via relasi studyClass)
        if ($request->filled('semester')) {
            $query->whereHas('studyClass', function ($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        $rawDistributions = $query->get();

        // GROUPING BARU: Berdasarkan Prodi - Semester - Angkatan - Shift
        // Ini akan menggabungkan Kelas A, B, C jika atribut di atas sama
        $distributions = $rawDistributions->groupBy(function ($item) {
            return $item->studyClass->prodi_id . '-' .
                $item->studyClass->semester . '-' .
                $item->studyClass->angkatan . '-' .
                $item->studyClass->shift;
        });

        // 4. Data untuk Modal Import (Study Classes pada periode terpilih)
        $study_classes = StudyClass::with('prodi')
            ->where('academic_period_id', $activePeriod->id)
            ->get();
        $activePeriod = $periods->firstwhere('is_active', true)->firstOrFail();
        $classes = StudyClass::where('academic_period_id', $activePeriod->id)->get();
        $dosens = User::select('id', 'name')->orderBy('name')->get();


        return view('content.distribution.index', compact('distributions', 'activePeriod', 'periods', 'study_classes', 'prodis', 'dosens', 'classes'));
    }


    // 2. FORM INPUT
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'study_class_id' => 'required|exists:study_classes,id',
            'course_id'      => 'required|exists:courses,id',
            'user_id'        => 'required|exists:users,id', // Dosen Utama Wajib
            'pddikti_user_id' => 'nullable|exists:users,id', // Dosen Tim Opsional
            'referensi'      => 'nullable|string',
            'luaran'         => 'nullable|string',
        ]);

        // 2. Cek Periode Aktif
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            return back()->with('error', 'Gagal: Tidak ada Periode Akademik yang aktif.');
        }

        // 3. Cek Duplikasi (Agar tidak ada matkul ganda di kelas yang sama pada periode ini)
        $exists = CourseDistribution::where([
            'academic_period_id' => $activePeriod->id,
            'study_class_id'     => $request->study_class_id,
            'course_id'          => $request->course_id,
        ])->exists();

        if ($exists) {
            return back()->with('error', 'Mata kuliah ini sudah didistribusikan di kelas tersebut!');
        }

        try {
            // 4. Simpan Data
            CourseDistribution::create([
                'academic_period_id' => $activePeriod->id,
                'study_class_id'     => $request->study_class_id,
                'course_id'          => $request->course_id,
                'user_id'            => $request->user_id,
                'pddikti_user_id'    => $request->pddikti_user_id,
                'referensi'          => $request->referensi,
                'luaran'             => $request->luaran,
            ]);

            return redirect()->route('distribusi-mata-kuliah.index')->with('success', 'Distribusi berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan server: ' . $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        // 
    }
    public function edit($id)
    {
        //
    }
    public function update(Request $request, $id)
    {
        $request->validate([

            'user_id'         => 'required|exists:users,id',
            'pddikti_user_id' => 'nullable|exists:users,id',
            'referensi'       => 'nullable|string',
            'luaran'          => 'nullable|string',
        ]);

        try {
            $distribution = CourseDistribution::findOrFail($id);

            // 2. Update Data
            $distribution->update([
                'user_id'         => $request->user_id,
                'pddikti_user_id' => $request->pddikti_user_id,
                'referensi'       => $request->referensi,
                'luaran'          => $request->luaran,
            ]);

            return back()->with('success', 'Data distribusi berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        try {
            $distribution = CourseDistribution::findOrFail($id);

            $distribution->delete();
            return redirect()->route('distribusi-mata-kuliah.index')
                ->with('success', 'Distribusi mata kuliah berhasil dihapus!');
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('distribusi-mata-kuliah.index')
                    ->with('error', 'Gagal menghapus: Data distribusi mata kuliah ini sedang digunakan.');
            }

            return redirect()->route('distribusi-mata-kuliah.index')->with('error', 'Terjadi kesalahan sistem saat menghapus data distribusi mata kuliah.');
        }
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

    public function generate(Request $request)
    {
        $request->validate(['period_id' => 'required|exists:academic_periods,id']);

        // 1. Ambil semua kelas di periode ini
        // Kita load relasi kurikulum agar tidak query ulang nanti (Eager Loading)
        $classes = StudyClass::where('academic_period_id', $request->period_id)->get();

        if ($classes->isEmpty()) {
            return back()->with('error', 'Tidak ada kelas pada periode ini. Import kelas dulu!');
        }

        $createdCount = 0;
        $existingCount = 0;

        // 2. Mulai Transaksi Database (Sekali di awal)
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // --- OPTIMASI: GROUPING ---
            // Kita kelompokkan kelas berdasarkan "Kurikulum & Semester" yang sama.
            // Tujuannya: Agar kita cukup query Mata Kuliah SEKALI saja per kelompok, 
            // bukan per kelas.
            $groupedClasses = $classes->groupBy(function ($item) {
                return $item->kurikulum_id . '-' . $item->semester;
            });

            // Loop per Kelompok (Misal: Kelompok TI-Smt1, Kelompok TI-Smt3)
            foreach ($groupedClasses as $groupKey => $classList) {

                // Ambil sampel data untuk cari matkul (ambil kelas pertama di grup)
                $sampleClass = $classList->first();

                // Query Matkul cukup SEKALI per kelompok
                $courses = Course::where('kurikulum_id', $sampleClass->kurikulum_id)
                    ->where('semester', $sampleClass->semester)
                    ->get();

                if ($courses->isEmpty()) continue; // Skip jika tidak ada matkul

                // Loop Kelas di dalam kelompok ini (In-Memory, cepat)
                foreach ($classList as $kelas) {
                    foreach ($courses as $course) {

                        // Gunakan firstOrCreate untuk keamanan data
                        $distribution = CourseDistribution::firstOrCreate(
                            [
                                'academic_period_id' => $request->period_id,
                                'study_class_id'     => $kelas->id,
                                'course_id'          => $course->id,
                            ],
                            [
                                'user_id' => null, // Default value
                            ]
                        );

                        if ($distribution->wasRecentlyCreated) {
                            $createdCount++;
                        } else {
                            $existingCount++;
                        }
                    }
                }
            }

            // 3. Commit Transaksi (Sekali di akhir)
            \Illuminate\Support\Facades\DB::commit();

            $message = "Sinkronisasi selesai! $createdCount data baru ditambahkan.";
            if ($existingCount > 0) {
                $message .= " ($existingCount data sudah ada/terupdate).";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            // Jika error, batalkan SEMUA perubahan
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }
    }

    // 2. FITUR EXPORT (Download Lembar Kerja)
    public function export(Request $request, $period_id)
    {
        // Nama file: distribusi_20251.xlsx
        $prodiId = $request->query('prodi_id');
        $semester = $request->query('semester');
        return Excel::download(
            new DistributionExport($period_id, $prodiId, $semester),
            'Distribusi Mata Kuliah_' . $period_id . '.xlsx'
        );
    }

    // 3. FITUR IMPORT UPDATE (Upload Balik)
    public function importUpdate(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new DistributionUpdateImport, $request->file('file'));
            return back()->with('success', 'Update Data Dosen Berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
}
