<?php

namespace App\Http\Controllers\master;

use App\Models\Room;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class RoomController extends Controller
{
    public function index()
    {
        $tags = [
            'general' => 'Umum / Standar',
            'computer' => 'Komputer (PC)',
            'network' => 'Alat Jaringan & IoT',
            'resto' => 'Dapur, Resto, & Kamar',
            'automotive' => 'Bengkel Otomotif',
            'medkit' => 'Peralatan Medis',
        ];
        $rooms = Room::with('prodis')
                ->orderBy('code', 'asc')
                ->get();
        $prodis = Prodi::all();

        return view('content.master.room.index', compact('rooms', 'prodis','tags'));
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:rooms,code',
            'name' => 'required|string',
            'capacity' => 'required|numeric',
            'type' => 'required|string',
            'prodi_ids' => 'nullable|array', 
            'prodi_ids.*' => 'exists:prodis,id',
            'location' => 'required|string',
            'facility_tag' => 'required_if:type,laboratorium|nullable|string'
        ]);

        $data = $request->except('prodi_ids');
        if ($request->type === 'teori') {
            $data['facility_tag'] = 'general';
        }

        $room = Room::create($data);

        if ($request->has('prodi_ids')) {
            $room->prodis()->sync($request->prodi_ids);
        }

        return redirect()->route('master.ruangan.index')->with('success', 'Ruangan berhasil dibuat');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Request $request, string $id)
    {
        
    }
        
    public function update(Request $request, string $id)
    {
        
        $room = Room::findOrFail($id);

        $request->validate([ 
            'code' => ['required', Rule::unique('rooms', 'code')->ignore($id)],
            'name' => 'required|string',
            'capacity' => 'required|numeric',
            'type' => 'required|string',
            'prodi_ids' => 'nullable|array', 
            'prodi_ids.*' => 'exists:prodis,id',
            'location' => 'required|string',
            'facility_tag' => 'required_if:type,laboratorium|nullable|string',
        ]);

        $data = $request->except('prodi_ids');
        if ($request->type === 'teori') {
            $data['facility_tag'] = 'general';
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
