<?php

namespace App\Http\Controllers\Master;

use App\Models\User;
use App\Models\Kurikulum;
use App\Models\StudyClass;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

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

        return view('content.master.classes.index', compact('classes','prodis','dosens', 'kurikulums', 'activePeriod'));
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
            'shift' => 'required|in:pagi,malam',
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
            'shift' => $request->shift,
        ]);

        return redirect()->route('master.kelas.index')->with('success', 'Kelas berhasil dibuat!');
    }

    public function edit(Request $request)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $studyClass = StudyClass::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'semester' => 'required|numeric',
            'prodi_id' => 'required|exists:prodis,id',
            'angkatan' => 'required|numeric',
            'total_students' => 'required|numeric',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'academic_advisor_id' => 'required|exists:users,id',
            'shift' => 'required|in:pagi,malam',
        ]);

        $data=$request->all();

        $studyClass->update($data);
        return redirect()->route('master.kelas.index')->with('success', 'Kelas berhasil diperbarui!');
    }

    public function destroy($id)
    {
        try {
            $studyClass = StudyClass::findOrFail($id);
            $studyClass->delete();
            return redirect()->route('master.kelas.index')->with('success', 'Kelas berhasil dihapus!');

        } catch (QueryException $e) {
           
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('master.kelas.index')
                    ->with('error', 'Gagal menghapus: Data Kelas');
            }

            return redirect()->route('master.kelas.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }

    
    public function generate(Request $request)
    {
        $request->validate([
            'source_period_id' => 'required|exists:academic_periods,id',
            'target_period_id' => 'required|exists:academic_periods,id|different:source_period_id',
        ]);

        $previousClasses = StudyClass::with('prodi') 
            ->where('academic_period_id', $request->source_period_id)
            ->get();

        if ($previousClasses->isEmpty()) {
            return back()->with('error', 'Tidak ada kelas di periode sumber.');
        }

        DB::beginTransaction(); 
        try {
            $count = 0;

            foreach ($previousClasses as $class) {                
                $maxSemester = $class->prodi->lama_studi ?? 8;
                if ($class->semester >= $maxSemester) {
                    continue; 
                }
                StudyClass::create([
                    'academic_period_id' => $request->target_period_id,
                    'semester'           => $class->semester + 1,
                    'name'               => $class->name,      
                    'angkatan'           => $class->angkatan,  
                    'prodi_id'           => $class->prodi_id,
                    'kurikulum_id'       => $class->kurikulum_id,
                    'academic_advisor_id'=> $class->academic_advisor_id,
                    'total_students'     => $class->total_students,
                ]);

                $count++;
            }

            DB::commit();
            return back()->with('success', "Sukses! Berhasil men-generate $count kelas untuk semester baru.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate kelas: ' . $e->getMessage());
        }
    }
    public function getKurikulumByProdi($prodiId)
    {
        $kurikulum = Kurikulum::where('prodi_id', $prodiId)
                        ->where('is_active', true) // Hanya tampilkan yg aktif
                        ->orderBy('tanggal', 'desc')
                        ->get();
                        
        return response()->json($kurikulum);
    }
}
