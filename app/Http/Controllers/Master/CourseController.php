<?php

namespace App\Http\Controllers\Master;

use App\Models\Prodi;
use App\Models\Course;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class CourseController extends Controller
{
    public function index()
    {
        $tags = [
            'general' => 'Umum / Standar',
            'computer' => 'Lab Komputer (PC)',
            'network' => 'Lab Jaringan & IoT',
            'resto' => 'Lab Restoran & Tata Hidang',
            'automotive' => 'Bengkel Otomotif',
            'hotel_fo' => 'Front Office Hotel',
        ];
        $prodis = Prodi::all();
        $courses = Course::with('kurikulum.prodi')->get();
        $kurikulums = Kurikulum::with('prodi')
                    ->where('is_active', true)
                    ->get();
        return view('content.master.courses.index', compact('courses','kurikulums','prodis', 'tags'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // Validasi Input SKS Pecahan
        $request->validate([
            'code' => 'required|unique:courses,code',
            'name' => 'required',
            'semester' => 'required|numeric',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'sks_teori' => 'required|numeric|min:0',
            'sks_praktik' => 'required|numeric|min:0',
            'sks_lapangan' => 'required|numeric|min:0',
            'required_tag' => 'required|string',
        ]);

        Course::create($request->all());
        return redirect()->route('master.mata-kuliah.index')->with('success', 'Mata Kuliah tersimpan!');
    }

    public function edit(Request $request)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $courses = Course::findOrFail($id);

        $request->validate([
            'code' => ['required', Rule::unique('courses', 'code')->ignore($id)],
            'name' => 'required|string',
            'semester' => 'required|numeric',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'sks_teori' => 'nullable|numeric|min:0',
            'sks_praktik' => 'nullable|numeric|min:0',
            'sks_lapangan' => 'nullable|numeric|min:0',
            'required_tag' => 'required|string',
        ]);

        $data = $request->all();

        $courses->update($data);
        return redirect()->route('master.mata-kuliah.index')->with('success', 'Mata Kuliah berhasil diperbarui!');
    }

    public function destroy($id)
    {
        try {
            $Courses = Course::findOrFail($id);
            $Courses->delete();
            return redirect()->route('master.mata-kuliah.index')->with('success', 'Mata Kuliah berhasil dihapus!');

        } catch (QueryException $e) {
           
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('master.mata-kuliah.index')
                    ->with('error', 'Gagal menghapus: Data Kelas Karna Masih Terpakai');
            }

            return redirect()->route('master.mata-kuliah.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }
}
