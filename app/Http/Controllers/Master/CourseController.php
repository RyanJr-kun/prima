<?php

namespace App\Http\Controllers\Master;

use App\Models\Prodi;
use App\Models\Course;
use App\Models\User;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Imports\CoursesImport;
use Illuminate\Validation\Rule;
use App\Services\SiakadSyncService;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CoursesTemplateExport;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $tags = [
            'general'         => 'Umum (AC, Proyektor, Board)',
            'computer'        => 'Komputer (PC / Lab Kom)',
            'network_iot'     => 'Jaringan, Sensor & IoT',
            'automotive'      => 'Mesin & Otomotif',
            'broadcasting'    => 'Studio, Kamera & Audio',
            'retail_sim'      => 'Simulasi Ritel & Kasir',
            'kitchen_resto'   => 'Dapur, Bar & Resto',
            'medical_record'  => 'Rekam Medis (Rak/Berkas)',
            'microscope'      => 'Mikroskop & Biologi',
            'chemistry'       => 'Kimia & Lemari Asam',
            'bio_molecular'   => 'PCR & Molekuler',
            'pharmacy_tool'   => 'Alat Farmasi & Cetak Tablet',
            'anatomy_bed'     => 'Anatomi & Bed Pasien',
        ];

        $user = Auth::user();
        /** @var User $user */
        $isKaprodi = $user->hasRole('kaprodi');
        $managedProdiId = null;

        if ($isKaprodi) {
            $managedProdi = $user->managedProdi;
            if ($managedProdi) {
                $managedProdiId = $managedProdi->id;
                $request->merge(['prodi_id' => $managedProdiId]);
            }
        }

        if ($isKaprodi && $managedProdiId) {
            $prodis = Prodi::where('id', $managedProdiId)->get();
        } else {
            $prodis = Prodi::all();
        }

        $query = Course::with('kurikulum.prodi');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('code', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('prodi_id')) {
            $query->whereHas('kurikulum', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $query->orderBy('code', 'asc');

        $courses = $query->get();

        $kurikulumQuery = Kurikulum::with('prodi')->where('is_active', true);

        if ($isKaprodi && $managedProdiId) {
            $kurikulumQuery->where('prodi_id', $managedProdiId);
        }

        $kurikulums = $kurikulumQuery->get();

        return view('content.master.courses.index', compact('courses', 'kurikulums', 'prodis', 'tags'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // Validasi Input SKS Pecahan
        $request->validate([
            'code'           => 'required|unique:courses,code',
            'name'           => 'required',
            'semester'       => 'required|numeric',
            'kurikulum_id'   => 'required|exists:kurikulums,id',
            'sks_teori'      => 'required|numeric|min:0',
            'sks_praktik'    => 'required|numeric|min:0',
            'sks_lapangan'   => 'required|numeric|min:0',
            'required_tags'   => 'nullable|array',
            'required_tags.*' => 'string',
        ]);

        $data = $request->all();

        if (empty($request->required_tags)) {
            $data['required_tags'] = ['general'];
        }

        Course::create($data);
        return redirect()->route('master.mata-kuliah.index')->with('success', 'Mata Kuliah tersimpan!');
    }

    public function edit(Request $request)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'code'           => ['required', Rule::unique('courses', 'code')->ignore($id)],
            'name'           => 'required|string',
            'semester'       => 'required|numeric',
            'kurikulum_id'   => 'required|exists:kurikulums,id',
            'sks_teori'      => 'nullable|numeric|min:0',
            'sks_praktik'    => 'nullable|numeric|min:0',
            'sks_lapangan'   => 'nullable|numeric|min:0',

            'required_tags'   => 'nullable|array',
            'required_tags.*' => 'string',
        ]);

        $data = $request->all();

        if (empty($request->required_tags)) {
            $data['required_tags'] = ['general'];
        }

        $course->update($data);
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

    public function downloadTemplate()
    {
        return Excel::download(new CoursesTemplateExport, 'template_mata_kuliah.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        set_time_limit(600);

        try {
            Excel::import(new CoursesImport, $request->file('file'));

            return back()->with('success', 'Import Berhasil!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $messages = '';

            foreach ($failures as $failure) {
                $messages .= 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors()) . '<br>';
            }

            return back()->with('error', 'Gagal Validasi: <br>' . $messages);
        } catch (\Exception $e) {

            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function syncSiakad(SiakadSyncService $service)
    {
        $result = $service->syncCourses(); // Panggil method courses

        if ($result['status']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
}
