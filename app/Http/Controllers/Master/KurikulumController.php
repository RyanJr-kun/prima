<?php

namespace App\Http\Controllers\Master;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Prodi;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use App\Services\SiakadSyncService;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class KurikulumController extends Controller
{
    public function index(Request $request)
    {
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

        $query = Kurikulum::with('prodi');

        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }

        $kurikulums = $query->get();

        if ($isKaprodi && $managedProdiId) {
            $prodis = Prodi::where('id', $managedProdiId)->get();
        } else {
            $prodis = Prodi::all();
        }

        return view('content.master.kurikulum.index', compact('kurikulums', 'prodis'));
    }

    public function create()
    {
        return view('content.kurikulum.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'tanggal' => 'required|date',
            'prodi_id' => 'required',
            'is_active' => 'required|boolean',
            'file_sk' => 'nullable|mimes:pdf|max:3072',
        ]);

        $data = $request->all();

        if ($request->hasFile('file_sk')) {
            $filePath = $request->file('file_sk')->store('kurikulums', 'public');
            $data['file_path'] = $filePath;
        }

        Kurikulum::create($data);
        return redirect()->route('master.kurikulum.index')->with('success', 'Kurikulum berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        $kurikulum = Kurikulum::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'tanggal' => 'required|date',
            'prodi_id' => 'required',
            'is_active' => 'required|boolean',
            'file_sk' => 'nullable|mimes:pdf|max:3072',
        ]);

        $data = $request->all();
        if ($request->hasFile('file_sk')) {
            if ($kurikulum->file_path && Storage::disk('public')->exists($kurikulum->file_path)) {
                Storage::disk('public')->delete($kurikulum->file_path);
            }

            // Simpan file baru
            $filePath = $request->file('file_sk')->store('kurikulums', 'public');
            $data['file_path'] = $filePath;
        }

        $kurikulum->update($data);
        return redirect()->route('master.kurikulum.index')->with('success', 'Kurikulum berhasil diperbarui');
    }

    public function destroy($id)
    {
        try {
            $kurikulum = Kurikulum::findOrFail($id);

            if ($kurikulum->file_sk && Storage::exists($kurikulum->file_sk)) {
                Storage::delete($kurikulum->file_sk);
            }

            $kurikulum->delete();

            return redirect()->route('master.kurikulum.index')
                ->with('success', 'Kurikulum berhasil dihapus!');
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('master.kurikulum.index')
                    ->with('error', 'Gagal menghapus: Data Kurikulum Masih Terpakai');
            }

            return redirect()->route('master.kurikulum.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }

    public function syncSiakad(SiakadSyncService $service)
    {
        $result = $service->syncKurikulums();

        if ($result['status']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
}
