<?php

namespace App\Http\Controllers\Master;

use App\Models\Room;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $tags = [
            // UMUM
            'general'         => 'Umum (AC, Proyektor, Board)',

            // IT & TEKNIK
            'computer'        => 'Komputer (PC / Lab Kom)',
            'network_iot'     => 'Jaringan, Sensor & IoT',
            'automotive'      => 'Mesin & Otomotif',
            'broadcasting'    => 'Studio, Kamera & Audio',

            // BISNIS & PERHOTELAN
            'retail_sim'      => 'Simulasi Ritel & Kasir',
            'kitchen_resto'   => 'Dapur, Bar & Resto',

            // KESEHATAN (MIK/TLM/FARMASI)
            'medical_record'  => 'Rekam Medis (Rak/Berkas)',
            'microscope'      => 'Mikroskop & Biologi',
            'chemistry'       => 'Kimia & Lemari Asam',
            'bio_molecular'   => 'PCR & Molekuler',
            'pharmacy_tool'   => 'Alat Farmasi & Cetak Tablet',
            'anatomy_bed'     => 'Anatomi & Bed Pasien',
        ];

        $query = Room::with('prodis');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $rooms = $query->orderBy('location', 'asc')->orderBy('code', 'asc')->get();

        $prodis = Prodi::all();

        return view('content.master.room.index', compact('rooms', 'prodis', 'tags'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'code'          => 'required|unique:rooms,code',
            'name'          => 'required|string',
            'capacity'      => 'required|numeric|min:1',
            'type'          => 'required|in:teori,laboratorium,aula',
            'location'      => 'required|in:kampus_1,kampus_2',
            'building'      => 'nullable|string',
            'floor'         => 'required|numeric',

            // Validasi Array Prodi (Pivot)
            'prodi_ids'     => 'nullable|array',
            'prodi_ids.*'   => 'exists:prodis,id',

            // Validasi Array Tags (JSON)
            'facility_tags'   => 'nullable|array',
            'facility_tags.*' => 'string',
        ]);

        $data = $request->except('prodi_ids');

        if (empty($request->facility_tags) && $request->type === 'teori') {
            $data['facility_tags'] = ['general'];
        }

        $room = Room::create($data);

        if ($request->filled('prodi_ids')) {
            $room->prodis()->sync($request->prodi_ids);
        }

        return redirect()->route('master.ruangan.index')->with('success', 'Ruangan berhasil dibuat');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Request $request, string $id) {}

    public function update(Request $request, string $id)
    {

        $room = Room::findOrFail($id);

        $request->validate([
            'code'          => ['required', Rule::unique('rooms', 'code')->ignore($id)],
            'name'          => 'required|string',
            'capacity'      => 'required|numeric|min:1',
            'type'          => 'required|in:teori,laboratorium,aula',
            'location'      => 'required|in:kampus_1,kampus_2',
            'building'      => 'nullable|string',
            'floor'         => 'required|numeric',

            'prodi_ids'     => 'nullable|array',
            'prodi_ids.*'   => 'exists:prodis,id',

            'facility_tags'   => 'nullable|array',
            'facility_tags.*' => 'string',
        ]);

        $data = $request->except('prodi_ids');

        if (empty($request->facility_tags) && $request->type === 'teori') {
            $data['facility_tags'] = ['general'];
        }

        $room->update($data);

        $room->prodis()->sync($request->input('prodi_ids', []));

        return redirect()->route('master.ruangan.index')->with('success', 'Ruangan berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        try {
            $room = Room::findOrFail($id);

            $room->delete();

            return redirect()->route('master.ruangan.index')
                ->with('success', 'Ruangan berhasil dihapus!');
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('master.ruangan.index')
                    ->with('error', 'Gagal menghapus: Data Ruangan Masih Terpakai');
            }

            return redirect()->route('master.ruangan.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }
}
